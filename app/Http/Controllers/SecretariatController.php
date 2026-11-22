<?php

namespace App\Http\Controllers;

use App\Models\Chamber;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class SecretariatController extends Controller
{
    private function definition(string $module): array
    {
        $definition = config('secretariat.modules')[$module] ?? null;
        abort_unless(is_array($definition), 404);
        return $definition;
    }

    private function administrative(Request $request): bool
    {
        return in_array($request->user()->role, ['admin', 'superadmin'], true);
    }

    private function mayWrite(Request $request, string $module): void
    {
        abort_if($module === 'team' && ! $this->administrative($request), 403);
    }

    private function lockOpen(): void
    {
        $chamber = Chamber::where('slug', 'secretariat')->lockForUpdate()->firstOrFail();
        abort_unless($chamber->is_open, 403);
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
        $query = DB::table('sec_'.$module);
        if ($request->filled('q')) {
            $fields = array_keys(array_filter($definition['fields'], fn ($field) => in_array($field['type'], ['text','email','textarea'], true)));
            $query->where(function ($sub) use ($fields, $request) {
                foreach ($fields as $field) $sub->orWhere($field, 'like', '%'.mb_substr($request->string('q'), 0, 150).'%');
                if (! $fields) $sub->whereRaw('1 = 0');
            });
        }
        foreach ($definition['fields'] as $field => $meta) {
            if (in_array($meta['type'], ['select','relation'], true) && $request->filled($field)) $query->where($field, $request->input($field));
        }
        if ($request->boolean('low') && $module === 'stock') $query->whereColumn('quantity', '<=', 'minimum_quantity');
        if ($request->boolean('overdue') && isset($definition['fields']['due_on'])) $query->whereDate('due_on','<',today())->whereNotIn('status',['paid','cancelled']);
        return $query;
    }

    private function cents($value): int
    {
        return (int) round((float) $value * 100);
    }

    private function validated(Request $request, string $module, ?int $record = null): array
    {
        $definition = $this->definition($module);
        $rules = [];
        foreach ($definition['fields'] as $field => $meta) {
            $rule = [$meta['required'] ? 'required' : 'nullable'];
            $rule[] = match ($meta['type']) {
                'email'=>'email','number','money'=>'numeric','date'=>'date','datetime'=>'date',
                'relation'=>'integer',default=>'string',
            };
            if (isset($meta['options'])) $rule[] = Rule::in(array_keys($meta['options']));
            if (in_array($meta['type'], ['text','textarea'], true)) $rule[] = 'max:'.($meta['type']==='textarea'?5000:255);
            $rules[$field] = $rule;
        }
        $data = $request->validate($rules);

        if ($module === 'orders') {
            $data['total_amount'] = $this->cents($data['quantity'] * $data['unit_price']) / 100;
        }
        if ($module === 'payments') {
            $paid = DB::table('sec_payments')->where('invoice_id', $data['invoice_id'])->when($record, fn($q)=>$q->where('id','!=',$record))->sum('amount');
            $invoice = DB::table('sec_invoices')->find($data['invoice_id']);
            if (! $invoice) throw ValidationException::withMessages(['invoice_id'=>'Facture inexistante.']);
            if ($this->cents($paid) + $this->cents($data['amount']) > $this->cents($invoice->amount)) throw ValidationException::withMessages(['amount'=>'Le paiement dépasse le solde de la facture.']);
        }
        if ($module === 'invoices') {
            $paid = $record ? DB::table('sec_payments')->where('invoice_id',$record)->sum('amount') : 0;
            if ($record && $this->cents($paid) > 0 && ($data['status'] !== 'issued' || $this->cents($data['amount']) < $this->cents($paid))) throw ValidationException::withMessages(['amount'=>'Une facture encaissée ne peut être annulée ni réduite.']);
        }
        if ($module === 'jobs') {
            $finished = $data['finished_at'] ?? null;
            $started = $data['started_at'] ?? null;
            if ($finished && $started && $finished < $started) throw ValidationException::withMessages(['finished_at'=>'La fin ne peut précéder le début.']);
        }
        return $data;
    }

    private function audit(Request $request, string $module, int $id, string $action, $before, $after): void
    {
        DB::table('chamber_audits')->insert([
            'chamber_slug'=>'secretariat','user_id'=>$request->user()->id,'module'=>$module,'record_id'=>$id,
            'action'=>$action,'before'=>$before?json_encode($before):null,'after'=>$after?json_encode($after):null,'created_at'=>now(),
        ]);
    }

    private function auditQuery()
    {
        return DB::table('chamber_audits')->leftJoin('users','users.id','=','chamber_audits.user_id')
            ->where('chamber_audits.chamber_slug','secretariat')->select('chamber_audits.*','users.name as actor')->orderByDesc('chamber_audits.id');
    }

    public function dashboard()
    {
        $kpis = [
            ['Commandes en production',DB::table('sec_orders')->where('status','in_progress')->count(),'orders',['status'=>'in_progress']],
            ['Commandes prêtes',DB::table('sec_orders')->where('status','ready')->count(),'orders',['status'=>'ready']],
            ['Travaux en contrôle',DB::table('sec_jobs')->where('status','quality')->count(),'jobs',['status'=>'quality']],
            ['Devis en attente',DB::table('sec_quotes')->where('status','sent')->count(),'quotes',['status'=>'sent']],
            ['Factures impayées',DB::table('sec_invoices')->where('status','issued')->count(),'invoices',['status'=>'issued']],
            ['Stock sous seuil',DB::table('sec_stock')->whereColumn('quantity','<=','minimum_quantity')->count(),'stock',['low'=>1]],
            ['Clients actifs',DB::table('sec_clients')->where('status','active')->count(),'clients',['status'=>'active']],
            ['Encaissements',number_format((float)DB::table('sec_payments')->sum('amount'),0,',',' '),'payments',[]],
        ];
        return view('back.secretariat.dashboard',[
            'kpis'=>$kpis,'modules'=>config('secretariat.modules'),
            'orders'=>DB::table('sec_orders')->orderByDesc('id')->limit(8)->get(),
            'jobs'=>DB::table('sec_jobs')->whereNotIn('status',['done','cancelled'])->orderByDesc('id')->limit(8)->get(),
            'audits'=>$this->auditQuery()->limit(10)->get(),
        ]);
    }

    public function index(Request $request, string $module)
    {
        $definition=$this->definition($module);
        $records=$this->query($request,$module)->orderByDesc('id')->paginate(20)->withQueryString();
        return view('back.secretariat.index',compact('module','definition','records')+['lookups'=>$this->lookups($definition)]);
    }

    public function create(Request $request, string $module)
    {
        $definition=$this->definition($module); $this->mayWrite($request,$module);
        $record=new \stdClass;
        foreach($definition['fields'] as $field=>$meta) $record->$field=$meta['type']==='relation'?$request->integer($field)?:null:null;
        return view('back.secretariat.form',compact('module','definition','record')+['lookups'=>$this->lookups($definition)]);
    }

    public function edit(Request $request,string $module,int $record)
    {
        $definition=$this->definition($module); $this->mayWrite($request,$module);
        $record=DB::table('sec_'.$module)->find($record); abort_unless($record,404);
        return view('back.secretariat.form',compact('module','definition','record')+['lookups'=>$this->lookups($definition)]);
    }

    public function show(string $module,int $record)
    {
        $definition=$this->definition($module); $record=DB::table('sec_'.$module)->find($record); abort_unless($record,404);
        $related=[];
        foreach(config('secretariat.modules') as $key=>$meta) foreach($meta['fields'] as $field=>$fieldMeta) if(($fieldMeta['table']??'')==='sec_'.$module) $related[]=['module'=>$key,'label'=>$meta['label'],'count'=>DB::table('sec_'.$key)->where($field,$record->id)->count()];
        return view('back.secretariat.show',compact('module','definition','record','related')+['lookups'=>$this->lookups($definition)]);
    }

    public function store(Request $request,string $module)
    {
        $this->mayWrite($request,$module);
        $id=DB::transaction(function()use($request,$module){
            $this->lockOpen(); $data=$this->validated($request,$module);
            $id=DB::table('sec_'.$module)->insertGetId($data+['created_by'=>$request->user()->id,'created_at'=>now(),'updated_at'=>now()]);
            if($module==='stock_movements') $this->applyStockMovement($id,$data);
            if($module==='payments') $this->refreshInvoiceStatus($data['invoice_id']);
            $this->audit($request,$module,$id,'create',null,$data);
            return $id;
        });
        return redirect()->route('secretariat.show',[$module,$id])->with('success','Enregistrement créé.');
    }

    public function update(Request $request,string $module,int $record)
    {
        $this->mayWrite($request,$module);
        DB::transaction(function()use($request,$module,$record){
            $this->lockOpen(); $before=DB::table('sec_'.$module)->where('id',$record)->lockForUpdate()->first(); abort_unless($before,404);
            $data=$this->validated($request,$module,$record);
            DB::table('sec_'.$module)->where('id',$record)->update($data+['updated_at'=>now()]);
            if($module==='payments') $this->refreshInvoiceStatus($data['invoice_id']);
            $this->audit($request,$module,$record,'update',$before,$data);
        });
        return redirect()->route('secretariat.show',[$module,$record])->with('success','Modifications enregistrées.');
    }

    public function destroy(Request $request,string $module,int $record)
    {
        abort_unless($this->administrative($request),403);
        try {
            DB::transaction(function()use($request,$module,$record){
                $this->lockOpen(); $before=DB::table('sec_'.$module)->where('id',$record)->lockForUpdate()->first(); abort_unless($before,404);
                DB::table('sec_'.$module)->where('id',$record)->delete();
                $this->audit($request,$module,$record,'delete',$before,null);
            });
        } catch(QueryException $exception) {
            return back()->withErrors(['delete'=>'Cet élément est utilisé par d’autres dossiers.']);
        }
        return redirect()->route('secretariat.index',$module)->with('success','Enregistrement supprimé.');
    }

    private function applyStockMovement(int $id,array $data): void
    {
        $stock=DB::table('sec_stock')->where('id',$data['stock_id'])->lockForUpdate()->first();
        if(! $stock) return;
        $quantity=(float)$data['quantity'];
        $new=$data['type']==='in'||$data['type']==='return'? (float)$stock->quantity+$quantity : (float)$stock->quantity-$quantity;
        if($new<0) throw ValidationException::withMessages(['quantity'=>'Stock insuffisant pour cette sortie.']);
        DB::table('sec_stock')->where('id',$stock->id)->update(['quantity'=>$new,'updated_at'=>now()]);
    }

    private function refreshInvoiceStatus(int $invoiceId): void
    {
        $invoice=DB::table('sec_invoices')->find($invoiceId); if(! $invoice) return;
        $paid=(float)DB::table('sec_payments')->where('invoice_id',$invoiceId)->sum('amount');
        if($paid>0) DB::table('sec_invoices')->where('id',$invoiceId)->update(['status'=>$paid >= (float)$invoice->amount?'paid':'issued','updated_at'=>now()]);
    }

    public function auditLog(){ return view('back.secretariat.audit',['audits'=>$this->auditQuery()->paginate(30)]); }

    public function reports()
    {
        $orders=DB::table('sec_orders')->select('sec_orders.*')
            ->selectSub(DB::table('sec_jobs')->selectRaw('count(*)')->whereColumn('order_id','sec_orders.id'),'job_count')
            ->selectSub(DB::table('sec_jobs')->selectRaw('count(*)')->whereColumn('order_id','sec_orders.id')->where('status','done'),'job_done')
            ->orderByDesc('id')->paginate(25);
        return view('back.secretariat.reports',compact('orders'));
    }

    public function export(Request $request,string $module)
    {
        $definition=$this->definition($module); $lookups=$this->lookups($definition);
        return response()->streamDownload(function()use($request,$module,$definition,$lookups){
            $output=fopen('php://output','w'); fwrite($output,"\xEF\xBB\xBF");
            fputcsv($output,['ID',...array_column($definition['fields'],'label')],';','"','');
            foreach($this->query($request,$module)->orderBy('id')->cursor() as $record){
                $row=[$record->id];
                foreach($definition['fields'] as $key=>$meta){
                    $value=$lookups[$key][$record->$key]??$meta['options'][$record->$key]??$record->$key??''; $value=(string)$value;
                    $row[]=preg_match('/^[\s]*[=+@\-]/u',$value)?"'".$value:$value;
                }
                fputcsv($output,$row,';','"','');
            } fclose($output);
        },'secretariat-'.$module.'-'.today()->format('Y-m-d').'.csv',['Content-Type'=>'text/csv; charset=UTF-8']);
    }
}
