<?php

use Kahlan\Plugin\Monkey;
use Sofa\LaravelKahlan\Env;
use Illuminate\Contracts\Foundation\Application;

describe('Laravel context for kahlan specs', function () {

    // Let's (re)create the DB as starting point,
    // because it gets wiped at the end, when
    // we use database migrations wrappers.
    before(function () {
        $this->laravel->artisan('migrate');
    });


    /*
    |--------------------------------------------------------------------------
    | You are free to use literally any of the Laravel features, eg. helpers
    |--------------------------------------------------------------------------
    */
    it('creates laravel app', function () {
        expect(app())->toBeAnInstanceOf(Application::class);
    });

    it('provides application in kahlan instance scope - as $this->app', function () {
        expect($this->app)->toBe(app());
        expect($this->laravel->app)->toBe(app());
    });

    it('binds to the container', function () {
        expect(app())->toBe($this->app);
        $stub = ['name' => 'stub'];
        $this->app->bind('some_service', function () use ($stub) {
            return ['name' => 'stub'];
        });
        expect($this->app->make('some_service'))->toEqual($stub);
    });

    it('recreates application for each single test', function () {
        // If you're not familiar with `toThrow()` matcher see
        // @link http://kahlan.readthedocs.io/en/latest/matchers/#classic-matchers
        //
        // Basically you'd wrap the code expected to throw the exception
        // in a closure and make expectation on the closure.
        expect(function () {
            $this->app['some_service'];
        })->toThrow(new ReflectionException('Class some_service does not exist', -1));
    });

    /*
    |--------------------------------------------------------------------------
    | The only difference from the original Laravel's TestCase
    | is in that here you call crawler/assertion methods on
    | `$this->laravel` helper object rather than `$this`.
    |--------------------------------------------------------------------------
    */
    context('It provides the same testing API as laravel TestCase', function () {
        it('crawls & asserts', function () {
            $this->laravel->get('/')
                          ->see('Laravel 5')
                          ->assertResponseOk();
        });

        it('interacts with database', function () {
            factory(App\User::class)->create(['email' => 'test@email.com']);
            $this->laravel->seeInDatabase('users', ['email' => 'test@email.com']);
        });

        it('interacts with session', function () {
            $this->laravel->withSession(['session_test' => 'working'])
                          ->get('/session-test')
                          ->see('working')
                          ->seeInSession(['session_test' => 'working']);
        });

        it('interacts with app services', function () {
            $this->laravel->expectsEvents('event_one', 'event_two')
                          ->doesntExpectEvents('event_three');

            event('event_one');
            event('event_two');
        });
    });

    /*
    |--------------------------------------------------------------------------
    | In order to use functionality provided by Laravel testing traits
    | eg. `use DatabaseTransactions` simply wrap your specs context
    | provided by `using()` and its alias `wrapEach()` functions.
    |
    | For example to add DatabaseTransactions you can use any of:
    |  - 'database.transactions' // dot notation
    |  - 'DatabaseTransactions'  // original laravel trait name
    |  - 'database transactions' // simple, human-readable string
    |  - 'database transaction'  // any of the above in SINGULAR form
    |
    |--------------------------------------------------------------------------
    */
    using(['database transactions'], function () {

        it('can wrap db operations in transaction', function () {
            factory(App\User::class)->create(['email' => 'some@email.com']);
            expect(App\User::where('email', 'some@email.com')->exists())->toBe(true);
        });

        it('then rolls back transaction', function () {
            expect(App\User::where('email', 'some@email.com')->exists())->toBe(false);
        });
    });

    using('without middleware', function () {
        it('runs without middleware on demand (auth)', function () {
            $this->laravel
                 ->get('/admin')
                 ->see('admin area for logged in user only');
        });

        it('runs without middleware on demand (guest)', function () {
            $this->laravel
                 ->actingAs(factory(App\User::class)->create())
                 ->get('/login')
                 ->see('login form for guests only');
        });
    });

    using('without events', function () {
        it('runs without events on demand', function () {
            app('events')->listen('test_event', function () {
                return 'fired';
            });
            expect(event('test_event'))->toBe(null);
        });
    });

    using(['database migrations'], function () {
        it('can migrate db for...', function () {
            factory(App\User::class)->create(['email' => 'some@email.com']);
            expect(App\User::where('email', 'some@email.com')->exists())->toBe(true);
        });

        it('...each single spec', function () {
            expect(App\User::where('email', 'some@email.com')->exists())->toBe(false);
        });
    });

});
