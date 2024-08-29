<?php

namespace DTApi\Services;

class ThrottleService
{
    public function ignoreThrottle($id)
    {
        $throttle = Throttles::find($id);
        if ($throttle) {
            $throttle->ignore = 1;
            $throttle->save();
            return ['success', 'Changes saved'];
        }
        return ['error', 'Throttle not found'];
    }


}