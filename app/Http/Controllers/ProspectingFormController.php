<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

class ProspectingFormController extends Controller
{
    public function index()
    {
        $selection = session('prospecting_selection');
        if (!$selection) {
            $oldInput = session()->getOldInput();
            if (!empty($oldInput['reference'])) {
                $selection = ['reference' => $oldInput['reference']];
            } elseif (!empty($oldInput['chamber_slug']) && !empty($oldInput['service_key'])) {
                $selection = [
                    'chamber_slug' => $oldInput['chamber_slug'],
                    'service_key' => $oldInput['service_key'],
                    'reference' => null,
                ];
            }
        }

        $selected = null;
        $questionnaire = null;
        $referenceRequest = null;
        $reference = $selection['reference'] ?? null;
        if ($selection && is_string($reference) && preg_match('/^SR-\d{6,}$/', $reference)) {
            $numericId = (int) substr($reference, 3);
            $referenceRequest = DB::table('public_service_requests')->find($numericId);
            if ($referenceRequest) {
                if (config('public_services.chambers.'.$referenceRequest->chamber_slug.'.services.'.$referenceRequest->service_key) !== null) {
                    [$selected, $questionnaire] = $this->serviceDefinition($referenceRequest->chamber_slug, $referenceRequest->service_key);
                }
            }
        } elseif ($selection && is_string($selection['chamber_slug'] ?? null) && is_string($selection['service_key'] ?? null)) {
            if (config('public_services.chambers.'.$selection['chamber_slug'].'.services.'.$selection['service_key']) !== null) {
                [$selected, $questionnaire] = $this->serviceDefinition($selection['chamber_slug'], $selection['service_key']);
            }
        }

        $answers = $this->defaults($questionnaire);
        $oldAnswers = session()->getOldInput('answers', []);
        if (is_array($oldAnswers) && $questionnaire) {
            foreach ($this->fields($questionnaire) as $field) {
                $value = $oldAnswers[$field['name']] ?? null;
                if ($field['type'] === 'checkboxes') {
                    if (is_array($value)) {
                        $answers[$field['name']] = $value;
                    }
                } elseif (is_string($value) || is_numeric($value)) {
                    $answers[$field['name']] = (string) $value;
                }
            }
        }

        $chambers = config('public_services.chambers');

        return view('back.prospecting-forms.index', [
            'prospects' => DB::table('public_service_prospects as prospects')
                ->leftJoin('users', 'users.id', '=', 'prospects.created_by')
                ->select('prospects.*', 'users.name as agent_name')
                ->orderByDesc('prospects.id')
                ->paginate(15, ['*'], 'prospects_page'),
            'pdfDownloads' => DB::table('prospect_pdf_downloads as downloads')
                ->join('public_service_prospects as prospects', 'prospects.id', '=', 'downloads.prospect_id')
                ->join('users', 'users.id', '=', 'downloads.downloaded_by')
                ->select(
                    'downloads.id as download_id',
                    'downloads.file_name',
                    'downloads.downloaded_at',
                    'prospects.id as prospect_id',
                    'prospects.service_name',
                    'prospects.chamber_slug',
                    'users.name as agent_name',
                )
                ->orderByDesc('downloads.downloaded_at')
                ->paginate(10, ['*'], 'downloads_page'),
            'chambers' => $chambers,
            'chamberCount' => DB::table('chambers')->count(),
            'serviceCount' => collect($chambers)->sum(fn (array $chamber) => count($chamber['services'] ?? [])),
            'submittedCount' => DB::table('public_service_prospects')->count(),
            'pdfDownloadCount' => DB::table('prospect_pdf_downloads')->count(),
            'publicRequestCount' => DB::table('public_service_requests')->count(),
            'selected' => $selected,
            'questionnaire' => $questionnaire,
            'answers' => $answers,
            'referenceRequest' => $referenceRequest,
            'unavailable' => $selected !== null && $questionnaire === null,
        ]);
    }

    public function resolve(Request $request)
    {
        $mode = $request->validate([
            'mode' => ['required', Rule::in(['reference', 'manual'])],
        ])['mode'];

        if ($mode === 'reference') {
            $reference = $request->validate([
                'reference' => ['required', 'string', 'regex:/^SR-\d{6,}$/'],
            ])['reference'];
            $numericId = (int) substr($reference, 3);
            $referenceRequest = DB::table('public_service_requests')->find($numericId);
            if (!$referenceRequest || $reference !== 'SR-'.str_pad((string) $referenceRequest->id, 6, '0', STR_PAD_LEFT)) {
                throw ValidationException::withMessages([
                    'reference' => 'Aucune demande ne correspond à cette référence.',
                ]);
            }

            $chamberSlug = $referenceRequest->chamber_slug;
            $serviceKey = $referenceRequest->service_key;
        } else {
            $chamberSlug = $request->validate([
                'chamber_slug' => ['required', 'string', Rule::in(array_keys(config('public_services.chambers')))],
            ])['chamber_slug'];
            $serviceKey = $request->validate([
                'service_key' => ['required', 'string'],
            ])['service_key'];
            $referenceRequest = null;
        }

        [$selected, $questionnaire] = $this->serviceDefinition($chamberSlug, $serviceKey);

        return redirect()->route('prospecting-forms.index')->with('prospecting_selection', [
            'reference' => $referenceRequest ? $request->input('reference') : null,
            'chamber_slug' => $selected['chamber_slug'],
            'service_key' => $selected['service_key'],
        ]);
    }

    public function store(Request $request)
    {
        $identity = $request->validate([
            'reference' => ['nullable', 'string', 'regex:/^SR-\d{6,}$/'],
            'chamber_slug' => ['required_without:reference', 'nullable', 'string', Rule::in(array_keys(config('public_services.chambers')))],
            'service_key' => ['required', 'string'],
        ]);

        if (!empty($identity['reference'])) {
            $numericId = (int) substr($identity['reference'], 3);
            $referenceRequest = DB::table('public_service_requests')->find($numericId);
            if (!$referenceRequest || $identity['reference'] !== 'SR-'.str_pad((string) $referenceRequest->id, 6, '0', STR_PAD_LEFT)) {
                throw ValidationException::withMessages([
                    'reference' => 'La demande liée à cette référence est introuvable.',
                ]);
            }
            $chamberSlug = $referenceRequest->chamber_slug;
            $serviceKey = $referenceRequest->service_key;
            if ($identity['service_key'] !== $serviceKey || ($identity['chamber_slug'] ?? $chamberSlug) !== $chamberSlug) {
                throw ValidationException::withMessages([
                    'reference' => 'La chambre et la prestation ne correspondent pas à cette référence.',
                ]);
            }
        } else {
            $chamberSlug = $identity['chamber_slug'];
            $serviceKey = $identity['service_key'];
            $referenceRequest = null;
        }

        [$selected, $questionnaire] = $this->serviceDefinition($chamberSlug, $serviceKey);
        if (!$questionnaire) {
            throw ValidationException::withMessages([
                'service_key' => 'Le formulaire de prospection de cette prestation n’est pas encore configuré.',
            ]);
        }

        $rules = ['answers' => ['required', 'array']];
        foreach ($this->fields($questionnaire) as $field) {
            $name = 'answers.'.$field['name'];
            $rules[$name] = [($field['required'] ?? false) ? 'required' : 'nullable'];

            if ($field['type'] === 'checkboxes') {
                $rules[$name][] = 'array';
                $rules[$name.'.*'] = ['string', Rule::in($field['options'])];
            } else {
                $rules[$name][] = match ($field['type']) {
                    'email' => 'email',
                    'url' => 'url',
                    'date' => 'date',
                    'number' => 'numeric',
                    default => 'string',
                };
                if (isset($field['options'])) {
                    $rules[$name][] = Rule::in($field['options']);
                }
                if ($field['type'] === 'number') {
                    $rules[$name][] = 'max:1000000000';
                } elseif (in_array($field['type'], ['text', 'textarea', 'email', 'tel', 'select', 'radio'], true)) {
                    $rules[$name][] = 'max:'.($field['type'] === 'textarea' ? 10000 : 1000);
                }
                if (isset($field['min'])) {
                    $rules[$name][] = 'min:'.$field['min'];
                }
            }
        }

        $validated = $request->validate($rules);
        $answers = [];
        foreach ($this->fields($questionnaire) as $field) {
            $answers[$field['name']] = $validated['answers'][$field['name']] ?? ($field['type'] === 'checkboxes' ? [] : '');
        }

        DB::table('public_service_prospects')->insert([
            'public_service_request_id' => $referenceRequest->id ?? null,
            'chamber_slug' => $selected['chamber_slug'],
            'service_key' => $selected['service_key'],
            'service_name' => $selected['service_name'],
            'answers' => json_encode($answers, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE),
            'created_by' => $request->user()->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('prospecting-forms.index')
            ->with('success', 'Le formulaire de prospection a été enregistré.');
    }

    public function show(int $prospectId)
    {
        $prospect = DB::table('public_service_prospects')->find($prospectId);
        abort_if(!$prospect, 404);
        $prospect->answers = json_decode($prospect->answers, true, 512, JSON_THROW_ON_ERROR);

        $questionnaire = config('prospecting_forms.'.$prospect->chamber_slug.'.'.$prospect->service_key);

        return view('back.prospecting-forms.show', [
            'prospect' => $prospect,
            'chambers' => config('public_services.chambers'),
            'fields' => $questionnaire ? $this->fields($questionnaire) : [],
        ]);
    }

    public function downloadPdf(Request $request, int $prospectId)
    {
        $prospect = DB::table('public_service_prospects')->find($prospectId);
        abort_if(!$prospect, 404);
        $prospect->answers = json_decode($prospect->answers, true, 512, JSON_THROW_ON_ERROR);

        $chambers = config('public_services.chambers');
        $questionnaire = config('prospecting_forms.'.$prospect->chamber_slug.'.'.$prospect->service_key);
        $fileName = 'EUNITAL-brief-'.$prospect->id.'-'.now()->format('Ymd-His').'.pdf';
        $filePath = 'briefs/'.now()->format('Y/m').'/'.$prospect->id.'/'.Str::uuid().'.pdf';
        $disk = Storage::disk('prospecting_private');
        $pdfContents = Pdf::loadView('back.prospecting-forms.pdf', [
            'prospect' => $prospect,
            'chamberName' => $chambers[$prospect->chamber_slug]['name'] ?? $prospect->chamber_slug,
            'fields' => $questionnaire ? $this->fields($questionnaire) : [],
            'questionnaire' => $questionnaire,
            'agentName' => DB::table('users')->where('id', $prospect->created_by)->value('name') ?? 'Agent inconnu',
        ])
            ->setPaper('a4')
            ->setOptions(['defaultFont' => 'DejaVu Sans', 'isRemoteEnabled' => false])
            ->output();

        try {
            if (!$disk->put($filePath, $pdfContents)) {
                throw new RuntimeException('Le fichier PDF n’a pas pu être archivé.');
            }

            DB::table('prospect_pdf_downloads')->insert([
                'prospect_id' => $prospect->id,
                'downloaded_by' => $request->user()->id,
                'file_path' => $filePath,
                'file_name' => $fileName,
                'downloaded_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (Throwable $exception) {
            if ($disk->exists($filePath)) {
                $disk->delete($filePath);
            }

            throw $exception;
        }

        return $disk->download($filePath, $fileName, [
            'Content-Type' => 'application/pdf',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function downloadArchivedPdf(int $downloadId)
    {
        $download = DB::table('prospect_pdf_downloads')->find($downloadId);
        abort_if(!$download, 404);

        $disk = Storage::disk('prospecting_private');
        abort_unless($disk->exists($download->file_path), 404, 'Le fichier PDF archivé est introuvable.');

        return $disk->download($download->file_path, $download->file_name, [
            'Content-Type' => 'application/pdf',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    private function serviceDefinition(string $chamberSlug, string $serviceKey): array
    {
        $chamber = config('public_services.chambers.'.$chamberSlug);
        $service = $chamber['services'][$serviceKey] ?? null;
        if (!$service) {
            throw ValidationException::withMessages([
                'service_key' => 'La prestation choisie ne correspond pas à la chambre.',
            ]);
        }

        return [[
            'chamber_slug' => $chamberSlug,
            'chamber_name' => $chamber['name'],
            'service_key' => $serviceKey,
            'service_name' => $service['label'],
        ], config('prospecting_forms.'.$chamberSlug.'.'.$serviceKey)];
    }

    private function defaults(?array $questionnaire): array
    {
        $answers = [];
        if (!$questionnaire) {
            return $answers;
        }

        foreach ($this->fields($questionnaire) as $field) {
            $answers[$field['name']] = $field['default'] ?? ($field['type'] === 'checkboxes' ? [] : '');
        }

        return $answers;
    }

    private function fields(array $questionnaire): array
    {
        return collect($questionnaire['sections'])
            ->flatMap(fn (array $section) => $section['fields'])
            ->values()
            ->all();
    }
}
