<?php

namespace WpStarter\Flow\Support\Facades;

use WpStarter\Flow\FlowManager;
use WpStarter\Flow\FlowRequest;
use WpStarter\Flow\FlowCollection;
use WpStarter\Flow\FlowRouter;

/**
 * @method static FlowManager configureStandalone() Configure Flow to use ArrayStore for both FlowData and FlowRegistry
 * @method static FlowManager configureLaravel() Configure Flow to use LaravelStore for both FlowData and FlowRegistry
 * @method static FlowManager configureWpStarter() Configure Flow to use WpStarterStore for both FlowData and FlowRegistry
 * @method static mixed run(FlowRequest $request) Run the flow with the given request and return the response
 * @method static FlowManager resetState() Reset the flow state for the next run
 * @method static FlowManager register(...$flows) Register flows or flow providers
 * @method static FlowManager withRoute($route) Add a route configuration file or closure
 * @method static FlowCollection flows() Get the current flow collection
 * @method static FlowRouter router() Get the flow router instance
 * @method static FlowManager changed($callback) Register callback when flow changed
 * @method static FlowManager instance() Get the FlowManager instance
 *
 * @see \WpStarter\Flow\FlowManager
 */
class Flow extends Facade
{

}