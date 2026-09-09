<?php

namespace Tests\Unit;

use App\Models\Application;
use App\Models\Category;
use App\Models\Company;
use App\Models\Cv;
use App\Models\Education;
use App\Models\Experience;
use App\Models\Job;
use App\Models\Language;
use App\Models\Project;
use App\Models\Skill;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Tests\TestCase;

class ModelRelationshipTest extends TestCase
{
    public function test_user_relationships(): void
    {
        $user = new User();
        $this->assertInstanceOf(HasOne::class, $user->company());
        $this->assertInstanceOf(HasMany::class, $user->companies());
        $this->assertInstanceOf(HasMany::class, $user->cvs());
        $this->assertInstanceOf(HasMany::class, $user->applications());
    }

    public function test_company_relationships(): void
    {
        $company = new Company();
        $this->assertInstanceOf(BelongsTo::class, $company->user());
        $this->assertInstanceOf(HasMany::class, $company->jobs());
    }

    public function test_category_relationships(): void
    {
        $category = new Category();
        $this->assertInstanceOf(HasMany::class, $category->jobs());
    }

    public function test_job_relationships(): void
    {
        $job = new Job();
        $this->assertInstanceOf(BelongsTo::class, $job->company());
        $this->assertInstanceOf(BelongsTo::class, $job->category());
        $this->assertInstanceOf(HasMany::class, $job->applications());
    }

    public function test_cv_relationships(): void
    {
        $cv = new Cv();
        $this->assertInstanceOf(BelongsTo::class, $cv->user());
        $this->assertInstanceOf(HasMany::class, $cv->educations());
        $this->assertInstanceOf(HasMany::class, $cv->experiences());
        $this->assertInstanceOf(HasMany::class, $cv->skills());
        $this->assertInstanceOf(HasMany::class, $cv->projects());
        $this->assertInstanceOf(HasMany::class, $cv->languages());
        $this->assertInstanceOf(HasMany::class, $cv->applications());
    }

    public function test_application_relationships(): void
    {
        $app = new Application();
        $this->assertInstanceOf(BelongsTo::class, $app->job());
        $this->assertInstanceOf(BelongsTo::class, $app->user());
        $this->assertInstanceOf(BelongsTo::class, $app->cv());
    }

    public function test_education_relationships(): void
    {
        $edu = new Education();
        $this->assertInstanceOf(BelongsTo::class, $edu->cv());
    }

    public function test_experience_relationships(): void
    {
        $exp = new Experience();
        $this->assertInstanceOf(BelongsTo::class, $exp->cv());
    }

    public function test_skill_relationships(): void
    {
        $skill = new Skill();
        $this->assertInstanceOf(BelongsTo::class, $skill->cv());
    }

    public function test_project_relationships(): void
    {
        $proj = new Project();
        $this->assertInstanceOf(BelongsTo::class, $proj->cv());
    }

    public function test_language_relationships(): void
    {
        $lang = new Language();
        $this->assertInstanceOf(BelongsTo::class, $lang->cv());
    }
}
