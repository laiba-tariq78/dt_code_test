<?php

namespace DTApi\Services;

use DTApi\Helpers\TeHelper;
use DTApi\Repository\BookingRepository;

class BookingService
{
    protected $bookingRepository;

    public function __construct(BookingRepository $bookingRepository)
    {
        $this->bookingRepository = $bookingRepository;
    }


    public function validateData($data)
    {
        $requiredFields = ['from_language_id', 'duration'];

        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                return $this->buildFailureResponse("Du måste fylla in alla fält", $field);
            }
        }

        if ($data['immediate'] == 'no') {
            if (empty($data['due_date']) || empty($data['due_time'])) {
                return $this->buildFailureResponse("Du måste fylla in alla fält", empty($data['due_date']) ? 'due_date' : 'due_time');
            }
            if (empty($data['customer_phone_type']) && empty($data['customer_physical_type'])) {
                return $this->buildFailureResponse("Du måste göra ett val här", 'customer_phone_type');
            }
        }

        return true;
    }


    public function applyFilters($query, array $requestdata)
    {
        if (isset($requestdata['lang']) && $requestdata['lang'] != '') {
            $query->whereIn('from_language_id', $requestdata['lang']);
        }
        if (isset($requestdata['status']) && $requestdata['status'] != '') {
            $query->whereIn('status', $requestdata['status']);
        }
        if (isset($requestdata['expired_at']) && $requestdata['expired_at'] != '') {
            $query->where('expired_at', '>=', $requestdata['expired_at']);
        }
        if (isset($requestdata['will_expire_at']) && $requestdata['will_expire_at'] != '') {
            $query->where('will_expire_at', '>=', $requestdata['will_expire_at']);
        }
        if (isset($requestdata['customer_email']) && count($requestdata['customer_email'])) {
            $users = DB::table('users')->whereIn('email', $requestdata['customer_email'])->pluck('id');
            if ($users->isNotEmpty()) {
                $query->whereIn('user_id', $users);
            }
        }
        if (isset($requestdata['translator_email']) && count($requestdata['translator_email'])) {
            $users = DB::table('users')->whereIn('email', $requestdata['translator_email'])->pluck('id');
            if ($users->isNotEmpty()) {
                $allJobIDs = DB::table('translator_job_rel')->whereNull('cancel_at')->whereIn('user_id', $users)->pluck('job_id');
                $query->whereIn('id', $allJobIDs);
            }
        }
        if (isset($requestdata['filter_timetype'])) {
            $this->applyDateFilters($query, $requestdata);
        }
        if (isset($requestdata['job_type']) && $requestdata['job_type'] != '') {
            $query->whereIn('job_type', $requestdata['job_type']);
        }
        if (isset($requestdata['physical'])) {
            $query->where('customer_physical_type', $requestdata['physical'])
                ->where('ignore_physical', 0);
        }
        if (isset($requestdata['phone'])) {
            $query->where('customer_phone_type', $requestdata['phone']);
            if (isset($requestdata['physical'])) {
                $query->where('ignore_physical_phone', 0);
            }
        }
        if (isset($requestdata['flagged'])) {
            $query->where('flagged', $requestdata['flagged'])
                ->where('ignore_flagged', 0);
        }
        if (isset($requestdata['distance']) && $requestdata['distance'] == 'empty') {
            $query->whereDoesntHave('distance');
        }
        if (isset($requestdata['salary']) && $requestdata['salary'] == 'yes') {
            $query->whereDoesntHave('user.salaries');
        }
        if (isset($requestdata['consumer_type']) && $requestdata['consumer_type'] != '') {
            $query->whereHas('user.userMeta', function ($q) use ($requestdata) {
                $q->where('consumer_type', $requestdata['consumer_type']);
            });
        }
        if (isset($requestdata['booking_type'])) {
            $bookingType = $requestdata['booking_type'] == 'physical' ? 'yes' : 'no';
            $query->where('customer_' . $requestdata['booking_type'] . '_type', $bookingType);
        }
    }

    public function applyDateFilters($query, array $requestdata)
    {
        if ($requestdata['filter_timetype'] == "created") {
            if (isset($requestdata['from']) && $requestdata['from'] != "") {
                $query->where('created_at', '>=', $requestdata["from"]);
            }
            if (isset($requestdata['to']) && $requestdata['to'] != "") {
                $query->where('created_at', '<=', $requestdata["to"] . " 23:59:00");
            }
        } elseif ($requestdata['filter_timetype'] == "due") {
            if (isset($requestdata['from']) && $requestdata['from'] != "") {
                $query->where('due', '>=', $requestdata["from"]);
            }
            if (isset($requestdata['to']) && $requestdata['to'] != "") {
                $query->where('due', '<=', $requestdata["to"] . " 23:59:00");
            }
        }
    }

    private function buildFailureResponse(string $message, $fields=null)
    {
        return [
            'status' => 'fail',
            'message' => $message,
            'field_name' => $fields,
        ];
    }
}