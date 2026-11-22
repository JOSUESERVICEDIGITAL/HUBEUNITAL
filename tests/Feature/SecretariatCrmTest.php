<?php

namespace Tests\Feature;

use App\Models\Chamber;
use App\Models\User;
use Database\Seeders\ChamberSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SecretariatCrmTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ChamberSeeder::class);
        Chamber::where('slug','secretariat')->update(['is_open'=>true]);
    }

    private function user(string $role='superadmin'): User
    {
        return User::factory()->create(['role'=>$role,'email_verified_at'=>now()]);
    }

    public function test_secretariat_pages_and_order_lifecycle_render_and_are_audited(): void
    {
        $this->actingAs($this->user());
        $this->get(route('secretariat.dashboard'))->assertOk();
        foreach(array_keys(config('secretariat.modules')) as $module){
            $this->get(route('secretariat.index',$module))->assertOk();
            $this->get(route('secretariat.create',$module))->assertOk();
        }
        $this->post(route('secretariat.store','clients'),[
            'name'=>'Entreprise Alpha','company'=>'Alpha SARL','phone'=>'70000000','type'=>'company','status'=>'active',
        ]);
        $client=DB::table('sec_clients')->first();
        $this->post(route('secretariat.store','orders'),[
            'reference'=>'SEC-001','client_id'=>$client->id,'service'=>'embroidery','title'=>'Broderie vestes',
            'quantity'=>12,'unit_price'=>2500,'total_amount'=>0,'priority'=>'high','status'=>'in_progress',
        ])->assertRedirect();
        $order=DB::table('sec_orders')->first();
        $this->assertSame(30000.0,(float)$order->total_amount);
        $this->post(route('secretariat.store','invoices'),[
            'reference'=>'FAC-001','client_id'=>$client->id,'order_id'=>$order->id,'amount'=>30000,
            'issued_on'=>today()->format('Y-m-d'),'status'=>'issued',
        ]);
        $invoice=DB::table('sec_invoices')->first();
        $this->post(route('secretariat.store','payments'),[
            'invoice_id'=>$invoice->id,'name'=>'Acompte','amount'=>10000,'payment_date'=>today()->format('Y-m-d'),'method'=>'mobile_money',
        ]);
        $this->assertDatabaseCount('chamber_audits',4);
        $this->get(route('secretariat.reports'))->assertOk()->assertSee('Broderie vestes');
    }

    public function test_stock_and_payment_limits_are_enforced(): void
    {
        $this->actingAs($this->user());
        $stockId=DB::table('sec_stock')->insertGetId([
            'name'=>'Fil blanc','category'=>'embroidery','unit'=>'roll','quantity'=>10,'minimum_quantity'=>2,'unit_cost'=>500,'status'=>'active','created_at'=>now(),'updated_at'=>now(),
        ]);
        $this->post(route('secretariat.store','stock_movements'),[
            'stock_id'=>$stockId,'type'=>'out','quantity'=>12,'unit_cost'=>500,'reason'=>'Production',
        ])->assertSessionHasErrors('quantity');
        $this->assertSame(10.0,(float)DB::table('sec_stock')->find($stockId)->quantity);

        $clientId=DB::table('sec_clients')->insertGetId([
            'name'=>'Client','phone'=>'1','type'=>'individual','status'=>'active','created_at'=>now(),'updated_at'=>now(),
        ]);
        $invoiceId=DB::table('sec_invoices')->insertGetId([
            'reference'=>'FAC-LIMIT','client_id'=>$clientId,'amount'=>5000,'issued_on'=>today()->format('Y-m-d'),'status'=>'issued','created_at'=>now(),'updated_at'=>now(),
        ]);
        $this->post(route('secretariat.store','payments'),[
            'invoice_id'=>$invoiceId,'name'=>'Solde','amount'=>6000,'payment_date'=>today()->format('Y-m-d'),'method'=>'cash',
        ])->assertSessionHasErrors('amount');
    }

    public function test_member_access_is_revocable_and_closure_blocks_crm(): void
    {
        $member=$this->user('author');
        DB::table('sec_team')->insert(['user_id'=>$member->id,'role'=>'printer','status'=>'active','created_at'=>now(),'updated_at'=>now()]);
        $this->actingAs($member)->get(route('secretariat.dashboard'))->assertOk();
        DB::table('sec_team')->where('user_id',$member->id)->update(['status'=>'inactive']);
        $this->get(route('secretariat.dashboard'))->assertForbidden();
        Chamber::where('slug','secretariat')->update(['is_open'=>false]);
        $this->actingAs($this->user())->get(route('secretariat.dashboard'))->assertForbidden();
    }
}
