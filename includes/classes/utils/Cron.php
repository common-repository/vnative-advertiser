<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class vnad_Cron {
	public function __construct() {

	}
    public function init() {
        add_filter( 'cron_schedules', array( $this, 'add_schedules'   ) );
        add_action( 'wp',             array( $this, 'schedule_Events' ) );
    }
    public function add_schedules( $schedules = array() ) {
        global $vnad;
		// Adds once weekly to the existing schedules.
		$schedules['weekly'] = array(
			'interval' => 604800,
			'display'  => $vnad->Lang->L('Once Weekly')
		);

		return $schedules;
	}
    public function schedule_Events() {
		$this->weekly_events();
		$this->daily_events();
	}
    private function weekly_events() {
		if ( ! wp_next_scheduled( 'vnad_weekly_scheduled_events' ) ) {
			wp_schedule_event( current_time( 'timestamp' ), 'weekly', 'vnad_weekly_scheduled_events' );
		}
	}
    private function daily_events() {
		if ( ! wp_next_scheduled( 'vnad_daily_scheduled_events' ) ) {
			wp_schedule_event( current_time( 'timestamp' ), 'daily', 'vnad_daily_scheduled_events' );
		}
	}
}
