<?php namespace Paxifi\PushNotifications\Controller;

use Config, Input;
use Paxifi\Support\Controller\BaseApiController;
use Paxifi\PushNotifications\Repository\PushDevice;
use Paxifi\PushNotifications\Exception\DeviceNotFoundException;

class PushDeviceController extends BaseApiController
{
    public function registerDeviceToken($driver = null)
    {
        try {
            \DB::beginTransaction();

            if (is_null($driver)) {
                $driver = $this->getAuthenticatedDriver();
            }

            $device = Input::get('device');

            if (!$device) {
                throw new DeviceNotFoundException();
            }

            if (PushDevice::checkToken($device['token'], $device['type'])) {
                $pushDevice = PushDevice::updateOrCreate($device, array_merge($device, ['driver_id' => $driver->id]));

                \DB::commit();

                return $this->setStatusCode(201)->respond($pushDevice);
            }

            return $this->errorWrongArgs($this->translator->trans('responses.errors.device.not_valid'));

        } catch (DeviceNotFoundException $e) {
            return $this->errorWrongArgs($this->translator->trans('responses.errors.device.not_found'));
        } catch (\Exception $e) {
            return $this->errorInternalError();
        }
    }
} 