<?php

class iPhoneCommunicator
{
	protected $ssm;

	// Default location if none is provided. Don't want to use this!
	public $default_location = array(
		'latitude'	=> 37.332063740371005,
		'longitude'	=> -122.03089445829391,
	);

	public $default_device = 0;

	public $devices = array();

	public function __construct($default_location=null)
	{
		if (defined('DEFAULT_DEVICE')) $this->default_device = DEFAULT_DEVICE;

		if (is_array($default_location) && isset($default_location['latitude']) && isset($default_location['longitude']))
		{
			$this->default_location = $default_location;
		/*
		} else {
			throw new Exception(TXT_ERROR_BAD_LOCATION);
		*/
		}

		$this->ssm = new Sosumi(ICLOUD_USER, ICLOUD_PASS);
		$this->devices = $this->ssm->devices;
	}

	// Send a basic alert to a device
	public function send_alert($message, $title='IPC Alert', $alarm=false, $device_id=null)
	{
		if (is_null($device_id)) $device_id = $this->default_device;
		if (isset($message))
		{
			if (is_object($this->ssm)) {
				error_log(sprintf(TXT_INFO_MESSAGE_SEND, $device_id, $title, $message));
			        $this->ssm->sendMessage($message, $alarm, $device_id, $title);
			} else {
				throw new Exception(TXT_ERROR_NO_SSM_OBJECT);
			}
		} else {
			throw new Exception(TXT_ERROR_BLANK_MESSAGE);
		}
	}

	// Determine distance between two points.
	// Returns: (float) distance in miles
	public function distance()
	{
		$opts = func_get_args();
		switch (count($opts))
		{
			case 0: // Use pre-set device and default location
				return self::distance($this->device_location($this->default_device), $this->default_location);
			break;
			case 1: // Use pre-set device and this location
				return self::distance($this->device_location($this->default_device), $opts[0]);
			break;
			case 2: // Use distance between two specified points
				$radians = M_PI/180;
				$lat1 = (float) $opts[0]['latitude'] * $radians;
				$lat2 = (float) $opts[1]['latitude'] * $radians;
				$lng1 = (float) $opts[1]['longitude'] * $radians;
				$lng2 = (float) $opts[1]['longitude'] * $radians;

				$r = 3960; // Radius in miles
				$dlat = $lat2 - $lat1;
				$dlng = $lng2 - $lng1;
				$a = sin($dlat / 2) * sin($dlat / 2) + cos($lat1) * cos($lat2) * sin($dlng / 2) * sin($dlng / 2);
				$c = 2 * atan2(sqrt($a), sqrt(1 - $a));
				return ($r * $c);
			break;
			default: // too many options
				throw new Exception(sprintf(TXT_ERROR_ARG_COUNT, __FUNCTION__, count($opts), 0, 2));
			break;
		}
	}

	// Internal method used to get lat/lon of device
	protected function device_location($device_id)
	{
		return $this->ssm->locate($device_id);
	}
}



