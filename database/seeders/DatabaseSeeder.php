<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Project;
use App\Models\Ticket;
use App\Models\TicketDetail;
use App\Models\User;
use App\Models\UserProfile;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     *  Seeds:
     *      - 3 companies
     *      - 5 users per company (
     *          1 admin -> admin@company{companyID}.test|password
     *          2 managers -> manager{1/2}@company{companyID}.test|password
     *          2 employees -> employee{1/2}@company{companyID}.test|password
     *      )
     *      - 4 projects per company
     *      - 15 tickets per project
     */
    public function run(): void
    {
        $companies = Company::factory()
            ->count(3)
            ->has(Project::factory()->count(4))
            ->create();

        $companies->each(function (Company $company) {
            $admin = User::factory()
                ->state([
                    'email' => 'admin@company'.$company->id.'.test',
                ])
                ->has(
                    UserProfile::factory()->state([
                        'company_id' => $company->id,
                        'role' => 'admin',
                    ])
                )
                ->create();

            $managers = User::factory()
                ->count(2)
                ->sequence(
                    ['email' => 'manager1@company'.$company->id.'.test'],
                    ['email' => 'manager2@company'.$company->id.'.test']
                )
                ->has(
                    UserProfile::factory()->state([
                        'company_id' => $company->id,
                        'role' => 'manager',
                    ])
                )
                ->create();

            $employees = User::factory()
                ->count(2)
                ->sequence(
                    ['email' => 'employee1@company'.$company->id.'.test'],
                    ['email' => 'employee2@company'.$company->id.'.test']
                )
                ->has(
                    UserProfile::factory()->state([
                        'company_id' => $company->id,
                        'role' => 'employee',
                    ])
                )
                ->create();

            $company->projects->each(function (Project $project) use ($admin, $employees) {
                Ticket::factory()
                    ->count(15)
                    ->create([
                        'project_id' => $project->id,
                        'created_by' => $admin->id,
                        'assigned_to' => $employees->random()->id,
                    ])
                    ->each(function (Ticket $ticket) {
                        TicketDetail::factory()
                            ->create(['ticket_id' => $ticket->id]);
                    });
            });
        });
    }
}
