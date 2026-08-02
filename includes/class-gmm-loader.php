<?php
/**
 * Register all actions and filters for the plugin.
 *
 * @package GMM
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class GMM_Loader
 *
 * Maintains collections of hooks and registers them with WordPress.
 * Future admin / public / student / teacher modules will register through this loader.
 */
class GMM_Loader {

	/**
	 * Actions to register with WordPress.
	 *
	 * @var array<int, array<string, mixed>>
	 */
	protected $actions = array();

	/**
	 * Filters to register with WordPress.
	 *
	 * @var array<int, array<string, mixed>>
	 */
	protected $filters = array();

	/**
	 * Add an action to the collection.
	 *
	 * @param string $hook          Hook name.
	 * @param object $component     Object instance.
	 * @param string $callback      Method name.
	 * @param int    $priority      Priority.
	 * @param int    $accepted_args Number of arguments.
	 * @return void
	 */
	public function add_action( $hook, $component, $callback, $priority = 10, $accepted_args = 1 ) {
		$this->actions = $this->add( $this->actions, $hook, $component, $callback, $priority, $accepted_args );
	}

	/**
	 * Add a filter to the collection.
	 *
	 * @param string $hook          Hook name.
	 * @param object $component     Object instance.
	 * @param string $callback      Method name.
	 * @param int    $priority      Priority.
	 * @param int    $accepted_args Number of arguments.
	 * @return void
	 */
	public function add_filter( $hook, $component, $callback, $priority = 10, $accepted_args = 1 ) {
		$this->filters = $this->add( $this->filters, $hook, $component, $callback, $priority, $accepted_args );
	}

	/**
	 * Build a hook registration entry.
	 *
	 * @param array<int, array<string, mixed>> $hooks         Existing hooks.
	 * @param string                           $hook          Hook name.
	 * @param object                           $component     Object instance.
	 * @param string                           $callback      Method name.
	 * @param int                              $priority      Priority.
	 * @param int                              $accepted_args Accepted args.
	 * @return array<int, array<string, mixed>>
	 */
	private function add( $hooks, $hook, $component, $callback, $priority, $accepted_args ) {
		$hooks[] = array(
			'hook'          => $hook,
			'component'     => $component,
			'callback'      => $callback,
			'priority'      => $priority,
			'accepted_args' => $accepted_args,
		);

		return $hooks;
	}

	/**
	 * Register collected filters and actions with WordPress.
	 *
	 * @return void
	 */
	public function run() {
		foreach ( $this->filters as $hook ) {
			add_filter(
				$hook['hook'],
				array( $hook['component'], $hook['callback'] ),
				$hook['priority'],
				$hook['accepted_args']
			);
		}

		foreach ( $this->actions as $hook ) {
			add_action(
				$hook['hook'],
				array( $hook['component'], $hook['callback'] ),
				$hook['priority'],
				$hook['accepted_args']
			);
		}
	}
}
