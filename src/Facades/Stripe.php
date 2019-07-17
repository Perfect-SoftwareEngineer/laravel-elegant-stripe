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

namespace CloudCreativity\LaravelStripe\Facades;

use CloudCreativity\LaravelStripe\Client;
use CloudCreativity\LaravelStripe\Connector;
use CloudCreativity\LaravelStripe\Contracts\Connect\AccountAdapterInterface;
use CloudCreativity\LaravelStripe\Log\Logger;
use CloudCreativity\LaravelStripe\Testing\ClientFake;
use CloudCreativity\LaravelStripe\Testing\StripeFake;
use Illuminate\Support\Facades\Facade;
use Stripe\StripeObject;

/**
 * Class Stripe
 *
 * @package CloudCreativity\LaravelStripe
 *
 * @method static Connector app()
 * @method static Connector account(string $accountId)
 * @method static void log(string $message, StripeObject|mixed $data, array $context = [])
 *
 * @method static void withQueue(StripeObject ...$objects)
 * @method static void assertInvoked(string $class, string $method, \Closure $args = null)
 * @method static void assertInvokedAt(int $index, string $class, string $method, \Closure $args = null)
 */
class Stripe extends Facade
{

    /**
     * Fake static calls to Stripe.
     *
     * @return StripeFake
     */
    public static function fake()
    {
        /**
         * Swapping the client stubs static calls to Stripe. This allows the entire Laravel
         * Stripe package to operate, with only the static calls to the Stripe package being
         * removed.
         */
        static::$app->instance(
            Client::class,
            $client = new ClientFake(static::$app->make('events'))
        );

        /**
         * We then swap in a Stripe service fake, that has our test assertions on it.
         * This extends the real Stripe service and doesn't overload anything on it,
         * so the service will operate exactly as expected.
         */
        static::swap($fake = new StripeFake(
            static::$app->make(AccountAdapterInterface::class),
            static::$app->make(Logger::class),
            $client
        ));

        return $fake;
    }

    /**
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'stripe';
    }
}
