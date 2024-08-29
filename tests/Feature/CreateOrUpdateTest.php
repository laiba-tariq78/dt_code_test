<?php
namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Type;
use App\Models\Company;
use App\Models\Department;
use App\Models\UserMeta;
use App\Models\UsersBlacklist;
use App\Models\UserLanguages;
use App\Models\Town;
use App\Models\UserTowns;

class CreateOrUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function testCreateUser()
    {
        $request = [
            'role' => 'customer',
            'name' => 'Laiba',
            'company_id' => '',
            'department_id' => '',
            'email' => 'laiba@example.com',
            'dob_or_orgid' => '2000-08-07',
            'phone' => '1234567890',
            'mobile' => '0987654321',
            'password' => 'password',
            'consumer_type' => 'paid',
            'username' => 'laiba_tariq',
            'post_code' => '12345',
            'address' => '123 Main St',
            'city' => 'Lahore',
            'town' => 'Punjab',
            'country' => 'Pakistan',
            'reference' => 'yes',
            'additional_info' => 'Additional info',
            'translator_ex' => [1, 2],
            'user_towns_projects' => [1, 2],
            'status' => '1'
        ];

        $response = $this->post('/create-or-update', $request);

        $response->assertStatus(200);

        $user = User::where('email', 'laiba@example.com')->first();
        $this->assertNotNull($user);

        $userMeta = UserMeta::where('user_id', $user->id)->first();
        $this->assertEquals('paid', $userMeta->consumer_type);

        $company = Company::where('name', 'Laiba Tariq')->first();
        $this->assertNotNull($company);

        $department = Department::where('name', 'Laiba Tariq')->first();
        $this->assertNotNull($department);

        $userBlacklist = UsersBlacklist::where('user_id', $user->id)->pluck('translator_id')->toArray();
        $this->assertContains(1, $userBlacklist);
        $this->assertContains(2, $userBlacklist);

        $userLanguages = UserLanguages::where('user_id', $user->id)->pluck('lang_id')->toArray();
        $this->assertContains(1, $userLanguages);

        $userTowns = UserTowns::where('user_id', $user->id)->pluck('town_id')->toArray();
        $this->assertContains(1, $userTowns);
    }

    public function testUpdateUser()
    {
        $user = User::factory()->create();
        $request = [
            'role' => 'translator',
            'name' => 'Laiba Tariq',
            'company_id' => '1',
            'department_id' => '1',
            'email' => 'laiba@example.com',
            'dob_or_orgid' => '2000-05-05',
            'phone' => '5555555555',
            'mobile' => '6666666666',
            'password' => 'newpassword',
            'translator_type' => 'volunteer',
            'worked_for' => 'yes',
            'organization_number' => '123456789',
            'gender' => 'female',
            'translator_level' => 'senior',
            'post_code' => '54321',
            'address' => '456 Elm St',
            'address_2' => 'Apt 1',
            'town' => 'updatedTown',
            'user_language' => [2],
            'user_towns_projects' => [3],
            'status' => '0'
        ];

        $response = $this->put('/create-or-update/' . $user->id, $request);

        $response->assertStatus(200);

        $updatedUser = User::find($user->id);
        $this->assertEquals('translator', $updatedUser->user_type);

        $userMeta = UserMeta::where('user_id', $user->id)->first();
        $this->assertEquals('professional', $userMeta->translator_type);
        $this->assertEquals('yes', $userMeta->worked_for);
        $this->assertEquals('123456789', $userMeta->organization_number);

        $userLanguages = UserLanguages::where('user_id', $user->id)->pluck('lang_id')->toArray();
        $this->assertContains(2, $userLanguages);

        $userTowns = UserTowns::where('user_id', $user->id)->pluck('town_id')->toArray();
        $this->assertContains(3, $userTowns);

        $this->assertEquals('0', $updatedUser->status);
    }
}
