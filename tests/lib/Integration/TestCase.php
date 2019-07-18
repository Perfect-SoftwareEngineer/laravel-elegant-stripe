<?php
/**
 * Copyright 2019 Cloud Creativity Limited
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace CloudCreativity\LaravelStripe\Tests\Integration;

use CloudCreativity\LaravelStripe\Facades\Stripe;
use CloudCreativity\LaravelStripe\ServiceProvider;
use CloudCreativity\LaravelStripe\Tests\TestExceptionHandler;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Database\Eloquent\Factory as ModelFactory;
use Illuminate\Foundation\Application;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{

    /**
     * @return void
     */
    protected function setUp()
    {
        parent::setUp();

        $this->counter = 0;

        /** Setup the test database */
        $this->app['migrator']->path(__DIR__ . '/../../database/migrations');
        $this->app->make(ModelFactory::class)->load(__DIR__ . '/../../database/factories');

        if (method_exists($this, 'withoutMockingConsoleOutput')) {
            $this->withoutMockingConsoleOutput();
        }

        $this->artisan('migrate', ['--database' => 'testbench']);
    }

    /**
     * Provider for all Stripe classes that are implemented via repositories.
     *
     * To support Laravel 5.4, we have to use an old version of the Stripe PHP
     * library. We therefore filter out any classes that do not exist.
     *
     * @todo filtering needs to be removed once we drop Laravel 5.4. The version
     * constraint for the stripe/stripe-php should always be set to support all
     * classes that are available on the latest version.
     *
     * @return array
     * @todo remove filtering once we are only on Stripe ^6.0.
     */
    public function classProvider()
    {
        return collect([
            'accounts' => [\Stripe\Account::class, 'accounts'],
            'charges' => [\Stripe\Charge::class, 'charges'],
            'events' => [\Stripe\Event::class, 'events'],
            'payment_intents' => [\Stripe\PaymentIntent::class, 'payment_intents'],
        ])->filter(function (array $values) {
            return class_exists($values[0]);
        })->all();
    }

    /**
     * Get package providers.
     *
     * To ensure this package works with Cashier, we also include
     * Cashier.
     *
     * @param Application $app
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            ServiceProvider::class,
        ];
    }

    /**
     * Get facade aliases.
     *
     * @param Application $app
     * @return array
     */
    protected function getPackageAliases($app)
    {
        return [
            'Stripe' => Stripe::class,
        ];
    }

    /**
     * Setup the test environment.
     *
     * @param Application $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        /** Include our default config. */
        $app['config']->set('stripe', require __DIR__ . '/../../../config/stripe.php');

        /** Setup a test database. */
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
    }

    /**
     * @return $this
     * @todo remove in Laravel ^5.5
     */
    protected function withoutExceptionHandling()
    {
        $this->instance(ExceptionHandler::class, $this->app->make(TestExceptionHandler::class));

        return $this;
    }

    /**
     * Load a stub.
     *
     * @param string $name
     * @return array
     */
    protected function stub($name)
    {
        return json_decode(
            file_get_contents(__DIR__ . '/../../stubs/' . $name . '.json'),
            true
        );
    }
}
