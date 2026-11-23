<?php

namespace App\Http\Controllers;

use App\Models\Chamber;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class FormationsController extends Controller
{
    private function definition(string $module): array
    {
        $definition = config('formations.modules')[$module] ?? null;
        abort_unless(is_array($definition), 404);
        return $definition;
    }

    private function administrative(Request $request): bool
    {
        return in_array($request->user()->role, ['admin', 'superadmin'], true);
    }

    private function mayWrite(Request $request, string $module): void
    {
        abort_if($module === 'team' && ! $this->administrative($request), 403, 'La gestion des accès est réservée aux administrateurs.');
    }

    private function lockOpen(): void
    {
        $chamber = Chamber::where('slug', 'formation')->lockForUpdate()->firstOrFail();
        abort_unless($chamber->is_open, 403, 'La chambre est fermée.');
    }

    private function lookups(array $definition): array
    {
        $lookups = [];
        foreach ($definition['fields'] as $field => $meta) {
            if (($meta['type'] ?? null) === 'relation') {
                $lookups[$field] = DB::table($meta['table'])->orderBy($meta['display'])->pluck($meta['display'], 'id')->all();
            }
        }
        return $lookups;
    }

    private function query(Request $request, string $module)
    {
        $definition = $this->definition($module);
        $query = DB::table('frm_'.$module);
        if ($request->filled('q')) {
            $fields = array_keys(array_filter($definition['fields'], fn ($field) => in_array($field['type'], ['text', 'email', 'textarea'], true)));
            $query->where(function ($sub) use ($fields, $request) {
                foreach ($fields as $field) $sub->orWhere($field, 'like', '%'.mb_substr($request->string('q'), 0, 150).'%');
                if (! $fields) $sub->whereRaw('1 = 0');
            });
        }
        foreach ($definition['fields'] as $field => $meta) {
            if (in_array($meta['type'], ['select', 'relation'], true) && $request->filled($field)) $query->where($field, $request->input($field));
        }
        if ($request->boolean('upcoming') && isset($definition['fields']['starts_at'])) $query->where('starts_at', '>=', now());
        if ($request->boolean('overdue') && isset($definition['fields']['ends_at'])) $query->where('ends_at', '<', now())->whereNotIn('status', ['completed', 'cancelled']);
        return $query;
    }

    private function validated(Request $request, string $module, ?int $record = null): array
    {
        $definition = $this->definition($module);
        $rules = [];
        foreach ($definition['fields'] as $field => $meta) {
            $rule = [$meta['required'] ? 'required' : 'nullable'];
            $rule[] = match ($meta['type']) {
                'email' => 'email',
                'url' => 'url',
                'number', 'money' => 'numeric',
                'date' => 'date',
                'datetime' => 'date',
                'relation' => 'integer',
                default => 'string',
            };
            if (isset($meta['options'])) $rule[] = Rule::in(array_keys($meta['options']));
            if (in_array($meta['type'], ['text', 'textarea', 'url'], true)) $rule[] = 'max:'.($meta['type'] === 'textarea' ? 5000 : ($meta['type'] === 'url' ? 2048 : 255));
            $rules[$field] = $rule;
        }
        if ($module === 'content') {
            $rules['content_file'] = ['nullable', 'file', 'mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,txt,csv,jpg,jpeg,png,mp4,webm,mp3,wav', 'max:102400'];
        }
        $data = $request->validate($rules);
        unset($data['content_file']);

        if ($module === 'content' && ! empty($data['document_url'])) {
            $scheme = strtolower((string) parse_url($data['document_url'], PHP_URL_SCHEME));
            if (! in_array($scheme, ['http', 'https'], true)) {
                throw ValidationException::withMessages(['document_url' => 'Le lien doit utiliser HTTP ou HTTPS.']);
            }
        }

        if ($module === 'cohorts') {
            if (! empty($data['ends_on']) && $data['ends_on'] < $data['starts_on']) throw ValidationException::withMessages(['ends_on' => 'La fin ne peut précéder le début.']);
        }
        if ($module === 'sessions') {
            if ($data['ends_at'] < $data['starts_at']) throw ValidationException::withMessages(['ends_at' => 'La fin ne peut précéder le début.']);
        }
        if ($module === 'applications' && ! empty($data['cohort_id'])) {
            $cohort = DB::table('frm_cohorts')->find($data['cohort_id']);
            if (! $cohort || $cohort->program_id != $data['program_id']) throw ValidationException::withMessages(['cohort_id' => 'La promotion doit appartenir au parcours sélectionné.']);
        }
        if ($module === 'enrollments') {
            if ((float) $data['fees_paid'] > (float) $data['fees_amount']) throw ValidationException::withMessages(['fees_paid' => 'Les frais payés ne peuvent dépasser les frais prévus.']);
            $cohort = DB::table('frm_cohorts')->find($data['cohort_id']);
            if ($cohort) {
                $count = DB::table('frm_enrollments')->where('cohort_id', $cohort->id)->whereIn('status', ['registered', 'active'])->when($record, fn ($q) => $q->where('id', '!=', $record))->count();
                $duplicate = DB::table('frm_enrollments')->where('cohort_id', $cohort->id)->where('learner_id', $data['learner_id'])->when($record, fn ($q) => $q->where('id', '!=', $record))->exists();
                if ($duplicate) throw ValidationException::withMessages(['learner_id' => 'Cet apprenant est déjà inscrit à cette promotion.']);
                if ($count >= $cohort->capacity && ! in_array($data['status'], ['abandoned', 'expelled', 'completed'], true)) throw ValidationException::withMessages(['cohort_id' => 'La capacité de cette promotion est atteinte.']);
            }
        }
        if ($module === 'attendance') {
            $session = DB::table('frm_sessions')->find($data['session_id']);
            if (! $session) throw ValidationException::withMessages(['session_id' => 'Session inexistante.']);
            $enrolled = DB::table('frm_enrollments')->where('cohort_id', $session->cohort_id)->where('learner_id', $data['learner_id'])->exists();
            if (! $enrolled) throw ValidationException::withMessages(['learner_id' => 'Cet apprenant n’est pas inscrit à la promotion de la session.']);
        }
        if ($module === 'evaluations') {
            if ((float) $data['score'] < 0 || (float) $data['score'] > (float) $data['max_score']) throw ValidationException::withMessages(['score' => 'La note doit être comprise entre 0 et la note maximale.']);
            $enrolled = DB::table('frm_enrollments')->where('cohort_id', $data['cohort_id'])->where('learner_id', $data['learner_id'])->exists();
            if (! $enrolled) throw ValidationException::withMessages(['learner_id' => 'Cet apprenant n’est pas inscrit à cette promotion.']);
        }
        if ($module === 'certificates') {
            $completed = DB::table('frm_enrollments')->where('cohort_id', $data['cohort_id'])->where('learner_id', $data['learner_id'])->where('status', 'completed')->exists();
            if ($data['status'] === 'issued' && ! $completed) throw ValidationException::withMessages(['status' => 'Le certificat ne peut être délivré qu’à un apprenant ayant terminé la promotion.']);
        }
        if ($module === 'talents') {
            $learner = DB::table('frm_learners')->find($data['learner_id']);
            if (! $learner) throw ValidationException::withMessages(['learner_id' => 'Apprenant inexistant.']);
        }
        return $data;
    }

    private function isUploadedContent(?string $path): bool
    {
        return $path !== null && str_starts_with($path, 'content/');
    }

    private function audit(Request $request, string $module, int $id, string $action, $before, $after): void
    {
        DB::table('chamber_audits')->insert([
            'chamber_slug' => 'formation', 'user_id' => $request->user()->id, 'module' => $module, 'record_id' => $id,
            'action' => $action, 'before' => $before ? json_encode($before) : null, 'after' => $after ? json_encode($after) : null, 'created_at' => now(),
        ]);
    }

    private function auditQuery()
    {
        return DB::table('chamber_audits')->leftJoin('users', 'users.id', '=', 'chamber_audits.user_id')
            ->where('chamber_audits.chamber_slug', 'formation')
            ->select('chamber_audits.*', 'users.name as actor')->orderByDesc('chamber_audits.id');
    }

    public function dashboard()
    {
        $kpis = [
            ['Parcours actifs', DB::table('frm_programs')->where('status', 'active')->count(), 'programs', ['status' => 'active']],
            ['Promotions actives', DB::table('frm_cohorts')->where('status', 'active')->count(), 'cohorts', ['status' => 'active']],
            ['Candidatures en étude', DB::table('frm_applications')->whereIn('status', ['received', 'review', 'interview'])->count(), 'applications', []],
            ['Apprenants actifs', DB::table('frm_enrollments')->where('status', 'active')->count(), 'enrollments', ['status' => 'active']],
            ['Sessions à venir', DB::table('frm_sessions')->where('starts_at', '>=', now())->whereNotIn('status', ['cancelled'])->count(), 'sessions', ['upcoming' => 1]],
            ['Certificats délivrés', DB::table('frm_certificates')->where('status', 'issued')->count(), 'certificates', ['status' => 'issued']],
            ['Talents disponibles', DB::table('frm_talents')->where('availability', 'immediate')->whereIn('status', ['eligible', 'shared'])->count(), 'talents', ['availability' => 'immediate']],
            ['Inscriptions avec impayés', DB::table('frm_enrollments')->whereColumn('fees_paid', '<', 'fees_amount')->count(), 'enrollments', []],
        ];
        return view('back.formations.dashboard', [
            'kpis' => $kpis, 'modules' => config('formations.modules'),
            'cohorts' => DB::table('frm_cohorts')->orderByDesc('starts_on')->limit(6)->get(),
            'sessions' => DB::table('frm_sessions')->whereNotIn('status', ['cancelled', 'completed'])->orderBy('starts_at')->limit(8)->get(),
            'audits' => $this->auditQuery()->limit(10)->get(),
        ]);
    }

    public function index(Request $request, string $module)
    {
        $definition = $this->definition($module);
        $records = $this->query($request, $module)->orderByDesc('id')->paginate(20)->withQueryString();
        return view('back.formations.index', compact('module', 'definition', 'records') + ['lookups' => $this->lookups($definition)]);
    }

    public function create(Request $request, string $module)
    {
        $definition = $this->definition($module);
        $this->mayWrite($request, $module);
        $record = new \stdClass;
        foreach ($definition['fields'] as $field => $meta) $record->$field = $meta['type'] === 'relation' ? $request->integer($field) ?: null : null;
        return view('back.formations.form', compact('module', 'definition', 'record') + ['lookups' => $this->lookups($definition)]);
    }

    public function edit(Request $request, string $module, int $record)
    {
        $definition = $this->definition($module);
        $this->mayWrite($request, $module);
        $record = DB::table('frm_'.$module)->find($record);
        abort_unless($record, 404);
        return view('back.formations.form', compact('module', 'definition', 'record') + ['lookups' => $this->lookups($definition)]);
    }

    public function show(string $module, int $record)
    {
        $definition = $this->definition($module);
        $record = DB::table('frm_'.$module)->find($record);
        abort_unless($record, 404);
        $related = [];
        foreach (config('formations.modules') as $key => $meta) {
            foreach ($meta['fields'] as $field => $fieldMeta) {
                if (($fieldMeta['table'] ?? '') === 'frm_'.$module) $related[] = ['module' => $key, 'label' => $meta['label'], 'field' => $field, 'count' => DB::table('frm_'.$key)->where($field, $record->id)->count()];
            }
        }
        return view('back.formations.show', compact('module', 'definition', 'record', 'related') + ['lookups' => $this->lookups($definition)]);
    }

    public function store(Request $request, string $module)
    {
        $this->mayWrite($request, $module);
        $storedFile = null;
        try {
            $id = DB::transaction(function () use ($request, $module, &$storedFile) {
                $this->lockOpen();
                $data = $this->validated($request, $module);
                if ($module === 'content' && $request->hasFile('content_file')) {
                    $storedFile = $request->file('content_file')->store('content', 'formations_private');
                    $data['document_url'] = $storedFile;
                }
                $id = DB::table('frm_'.$module)->insertGetId($data + ['created_by' => $request->user()->id, 'created_at' => now(), 'updated_at' => now()]);
                $this->audit($request, $module, $id, 'create', null, $data);
                return $id;
            });
        } catch (\Throwable $exception) {
            if ($storedFile !== null) Storage::disk('formations_private')->delete($storedFile);
            throw $exception;
        }
        return redirect()->route('formations.show', [$module, $id])->with('success', 'Enregistrement créé.');
    }

    public function update(Request $request, string $module, int $record)
    {
        $this->mayWrite($request, $module);
        $storedFile = null;
        $obsoleteFile = null;
        try {
            DB::transaction(function () use ($request, $module, $record, &$storedFile, &$obsoleteFile) {
                $this->lockOpen();
                $before = DB::table('frm_'.$module)->where('id', $record)->lockForUpdate()->first();
                abort_unless($before, 404);
                $data = $this->validated($request, $module, $record);
                if ($module === 'content') {
                    if ($request->hasFile('content_file')) {
                        $storedFile = $request->file('content_file')->store('content', 'formations_private');
                        $data['document_url'] = $storedFile;
                    } elseif (empty($data['document_url']) && $this->isUploadedContent($before->document_url)) {
                        $data['document_url'] = $before->document_url;
                    }
                    if ($this->isUploadedContent($before->document_url) && $before->document_url !== ($data['document_url'] ?? null)) {
                        $obsoleteFile = $before->document_url;
                    }
                }
                DB::table('frm_'.$module)->where('id', $record)->update($data + ['updated_at' => now()]);
                $this->audit($request, $module, $record, 'update', $before, $data);
            });
        } catch (\Throwable $exception) {
            if ($storedFile !== null) Storage::disk('formations_private')->delete($storedFile);
            throw $exception;
        }
        if ($obsoleteFile !== null) Storage::disk('formations_private')->delete($obsoleteFile);
        return redirect()->route('formations.show', [$module, $record])->with('success', 'Modifications enregistrées.');
    }

    public function destroy(Request $request, string $module, int $record)
    {
        abort_unless($this->administrative($request), 403);
        $deletedFile = null;
        try {
            DB::transaction(function () use ($request, $module, $record, &$deletedFile) {
                $this->lockOpen();
                $before = DB::table('frm_'.$module)->where('id', $record)->lockForUpdate()->first();
                abort_unless($before, 404);
                if ($module === 'content' && $this->isUploadedContent($before->document_url)) $deletedFile = $before->document_url;
                DB::table('frm_'.$module)->where('id', $record)->delete();
                $this->audit($request, $module, $record, 'delete', $before, null);
            });
        } catch (QueryException $exception) {
            return back()->withErrors(['delete' => 'Cet élément est utilisé par d’autres dossiers. Retirez ses dépendances avant de le supprimer.']);
        }
        if ($deletedFile !== null) Storage::disk('formations_private')->delete($deletedFile);
        return redirect()->route('formations.index', $module)->with('success', 'Enregistrement supprimé.');
    }

    public function downloadContent(int $record)
    {
        $content = DB::table('frm_content')->find($record);
        abort_unless($content && $this->isUploadedContent($content->document_url), 404);

        $disk = Storage::disk('formations_private');
        abort_unless($disk->exists($content->document_url), 404);

        $extension = pathinfo($content->document_url, PATHINFO_EXTENSION);
        $filename = \Illuminate\Support\Str::slug($content->name).($extension ? '.'.$extension : '');
        return $disk->download($content->document_url, $filename, ['X-Content-Type-Options' => 'nosniff']);
    }

    public function auditLog() { return view('back.formations.audit', ['audits' => $this->auditQuery()->paginate(30)]); }

    public function reports()
    {
        $cohorts = DB::table('frm_cohorts')->select('frm_cohorts.*')
            ->selectSub(DB::table('frm_enrollments')->selectRaw('count(*)')->whereColumn('cohort_id', 'frm_cohorts.id'), 'learner_count')
            ->selectSub(DB::table('frm_enrollments')->selectRaw('count(*)')->whereColumn('cohort_id', 'frm_cohorts.id')->where('status', 'completed'), 'completed_count')
            ->selectSub(DB::table('frm_evaluations')->selectRaw('count(*)')->whereColumn('cohort_id', 'frm_cohorts.id'), 'evaluation_count')
            ->selectSub(DB::table('frm_certificates')->selectRaw('count(*)')->whereColumn('cohort_id', 'frm_cohorts.id')->where('status', 'issued'), 'certificate_count')
            ->orderByDesc('id')->paginate(25);
        return view('back.formations.reports', compact('cohorts'));
    }

    public function export(Request $request, string $module)
    {
        $definition = $this->definition($module);
        $lookups = $this->lookups($definition);
        return response()->streamDownload(function () use ($request, $module, $definition, $lookups) {
            $output = fopen('php://output', 'w');
            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, ['ID', ...array_column($definition['fields'], 'label')], ';', '"', '');
            foreach ($this->query($request, $module)->orderBy('id')->cursor() as $record) {
                $row = [$record->id];
                foreach ($definition['fields'] as $key => $meta) {
                    $value = $lookups[$key][$record->$key] ?? $meta['options'][$record->$key] ?? $record->$key ?? '';
                    $value = (string) $value;
                    $row[] = preg_match('/^[\s]*[=+@\-]/u', $value) ? "'".$value : $value;
                }
                fputcsv($output, $row, ';', '"', '');
            }
            fclose($output);
        }, 'formations-'.$module.'-'.today()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
