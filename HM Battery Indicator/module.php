<?php
declare(strict_types=1);

require_once __DIR__ . '/../libs/HMDataClass.php';
require_once __DIR__ . '/../libs/vP_Toolbox.php';
require_once __DIR__ . '/../libs/UserNameInterface.php';

class HomeMaticBatteryIndicator extends IPSModule
{
	use VariableProfileHelper;
	use UserNameInterface;

	public function Create()
	{
		parent::Create();

		$this->RegisterPropertyString('ALARM_ICON', 'Battery');
		$this->RegisterPropertyString('ACTIVE_ALARMS', '[]');     // Property to ID of all active alarm instances
	}

	public function ApplyChanges()
	{
		parent::ApplyChanges();
	}

	public function RequestAction($Ident, $Value)
	{
		switch ($Ident) {
			default:
				throw new Exception('RequestAction: Invalid Ident ' . $Ident);
		}
	}

	public function SetAlarmActive($instanceID)
	{
		// Get list of all registered alarms
		$list = json_decode($this->ReadPropertyString('ACTIVE_ALARMS'), true);

		// Check if this instance is already in the list. If not, add instance to list...
		if (!in_array($instanceID, $list)) {
			// Does not exist --> insert entry
			$list[] = $instanceID;
		}
		// We have at least one active battery alarm. Update Icon if necessarry
		$this->UpdateIcon(true);
		
		// Save list in property
		IPS_SetProperty($this->InstanceeID, "ACTIVE_ALARMS", json_encode($list));
		// Update Summary with number of registerd active alarms
		$counter = count($list);
		$this->SetSummary(sprintf($this->Translate("We have %s active alarm%s"), ($counter == 0 ? "no" : (string)$counter), ($counter != 1 ? 's' : '')));

		// Save changed properties
		IPS_ApplyChanges($this->InstanceID);

		return true;
	}

	public function SetAlarmCleared($instanceID)
	{
		// Get list of all registered alarms
		$list = json_decode($this->ReadPropertyString('ACTIVE_ALARMS'), true);
		
		// Check if this instance is in the list of active alarm. When existing, remove from list
		$pos = array_search($instanceID, $list);
		if ($pos !== false and count($list) > 1) {
			// Remove the list element
			unset($list[$pos]);
			// and renumber the rest of the list (if any)
			$new = [];
			foreach($list as $entry) {
				$new[] = $entry;
			}
			$list = $new
			}
		} elseif ($pos !== false) {
			// Last alarm to clear...
			$list = [];
			$this->UpdateIcon(false);
		}
		
		// Update Summary with number of registerd active alarms
		$counter = count($list);
		$this->SetSummary(sprintf($this->Translate("We have %s active alarm%s"), ($counter == 0 ? "no" : (string)$counter), ($counter != 1 ? 's' : '')));

		// Save changed properties
		IPS_ApplyChanges($this->InstanceID);

		return;
	}

	public function InstanceAlarmPresent($instanceID): bool
	{
		// Get list of all registered alarms
		$list = json_decode($this->ReadPropertyString('ACTIVE_ALARMS'), true);

		// Check if this instance is in the list. Return the result (true / false)
		return in_array($instanceID, $list);
	}

	private function UpdateIcon(bool $NewState)
	{
		// Get actual list of raised alarms
		$list = json_decode($this->ReadPropertyString('ACTIVE_ALARMS'), true);
		if (count($list) > 0) {
			// Set ICON of this instance to the selected alarm icon
			IPS_SetIcon($this->InstanceID, $this->ReadPropertyString('ALARM_ICON'));
		} else {
			// Set ICON of this instance to transparent
			IPS_SetIcon($this->InstanceID, "transparent");
		}
		
		return;
	}
}