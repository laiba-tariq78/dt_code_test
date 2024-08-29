<?php

namespace DTApi\Http\Controllers;

use DTApi\Models\Job;
use DTApi\Http\Requests;
use DTApi\Models\Distance;
use Illuminate\Http\Request;
use DTApi\Repository\BookingRepository;

/**
 * Class BookingController
 * @package DTApi\Http\Controllers
 */
class BookingController extends Controller
{

    /**
     * @var BookingRepository
     */
    protected $repository;

    /**
     * BookingController constructor.
     * @param BookingRepository $bookingRepository
     */
    public function __construct(BookingRepository $bookingRepository)
    {
        $this->repository = $bookingRepository;
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function index(Request $request)
    {
        if($user_id = $request->get('user_id')) {

            $response = $this->repository->getUsersJobs($user_id);

        } elseif ($request->__authenticatedUser->hasRole(['admin', 'super-admin'])) {  //User Laravel's Spatie package for roles and permissions instead of env

            $response = $this->repository->getAll($request);
        }

        return response($response);
    }

    /**
     * @param $id
     * @return mixed
     */
    public function show($id)
    {
        $job = $this->repository->with('translatorJobRel.user')->findOrFail($id); //more optimal

        return response($job);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function store(Request $request)
    {
        $data = $request->all();

        //validate request here
        $validatedData = $request->validate([
            'user_email' => 'required|email',
            'user_name' => 'required|string',
            'address' => 'nullable|string',
            //similarly other rules
        ]);

        $data = $validatedData; // Use validated data
        $response = $this->repository->store($request->__authenticatedUser, $data);

        return response($response);

    }

    /**
     * @param $id
     * @param Request $request
     * @return mixed
     */
    public function update($id, Request $request)
    {
        $data = $request->except(['_token', 'submit']); // Use except directly on request data
        //similarly request didn't validate here
        $cuser = $request->__authenticatedUser;
        $response = $this->repository->updateJob($id, $data, $cuser);

        return response($response);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function immediateJobEmail(Request $request)
    {
        $adminSenderEmail = config('app.adminemail');
        $data = $request->all();

        $validatedData = $request->validate([
            'email' => 'required|email',
            //similarly other rules
        ]);
        $data = $validatedData;

        try {
            $response = $this->repository->storeJobEmail($data);
        } catch (\Exception $e) {
            // Handle any exceptions
            return response(['status' => 'fail', 'message' => 'An error occurred while processing the email'], 500);
        }

        return response($response);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function getHistory(Request $request)
    {
        if($user_id = $request->get('user_id')) {

            $response = $this->repository->getUsersJobsHistory($user_id, $request);
            return response($response);
        }

        //instead of null here return error message
        return response(['status' => 'fail', 'message' => 'User ID is required'], 400);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function acceptJob(Request $request)
    {
        $data = $request->all();
        // data not validated
        $user = $request->__authenticatedUser;
        //try catch here
        $response = $this->repository->acceptJob($data, $user);

        return response($response);
    }

    public function acceptJobWithId(Request $request)
    {
        $data = $request->get('job_id');
        $user = $request->__authenticatedUser;

        $response = $this->repository->acceptJobWithId($data, $user);

        //handle success and error both
        return response($response);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function cancelJob(Request $request)
    {
        $data = $request->all();
        //request not validated
        $user = $request->__authenticatedUser;

        $response = $this->repository->cancelJobAjax($data, $user);
        //handle error success both
        return response($response);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function endJob(Request $request)
    {
        //same as above function
        $data = $request->all();

        $response = $this->repository->endJob($data);

        return response($response);

    }

    public function customerNotCall(Request $request)
    {
        //same as above function
        $data = $request->all();

        $response = $this->repository->customerNotCall($data);

        return response($response);

    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function getPotentialJobs(Request $request)
    {
        //same as above function
        $data = $request->all();
        $user = $request->__authenticatedUser;

        $response = $this->repository->getPotentialJobs($user);

        return response($response);
    }

    public function distanceFeed(Request $request)
    {
        //Extract only required fields
        $data = $request->only(['distance', 'time', 'jobid', 'session_time', 'flagged', 'manually_handled', 'by_admin', 'admincomment']);

        //Instead of too much if else optimise the code using ternary operator
        $distance = $data['distance'] ?? '';
        $time = $data['time'] ?? '';
        $jobid = $data['jobid'] ?? null;
        $session = $data['session_time'] ?? '';
        $flagged = ($data['flagged'] ?? 'false') === 'true' ? 'yes' : 'no';
        $manually_handled = ($data['manually_handled'] ?? 'false') === 'true' ? 'yes' : 'no';
        $by_admin = ($data['by_admin'] ?? 'false') === 'true' ? 'yes' : 'no';
        $admincomment = $data['admincomment'] ?? '';

        if ($flagged === 'yes' && empty($admincomment)) {
            return response("Please, add comment", 400);
        }

        // Update Distance table if relevant data exists
        if ($time || $distance) {
            Distance::where('job_id', $jobid)
                ->update(['distance' => $distance, 'time' => $time]);
        }

        // Update Job table if any of these fields are set
        if ($admincomment || $session || $flagged || $manually_handled || $by_admin) {
            Job::where('id', $jobid)
                ->update([
                    'admin_comments' => $admincomment,
                    'flagged' => $flagged,
                    'session_time' => $session,
                    'manually_handled' => $manually_handled,
                    'by_admin' => $by_admin
                ]);
        }

        return response('Record updated!');
    }

    public function reopen(Request $request)
    {
        $data = $request->all();
        $response = $this->repository->reopen($data);

        return response($response);
    }

    public function resendNotifications(Request $request)
    {
        $data = $request->all();
        $job = $this->repository->find($data['jobid']);
        $job_data = $this->repository->jobToData($job);
        //better to use try catch for exception handling or any other error
        $this->repository->sendNotificationTranslator($job, $job_data, '*');

        return response(['success' => 'Push sent']);
    }

    /**
     * Sends SMS to Translator
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function resendSMSNotifications(Request $request)
    {
        $data = $request->all();
        $job = $this->repository->find($data['jobid']);
        $job_data = $this->repository->jobToData($job);

        try {
            $this->repository->sendSMSNotificationToTranslator($job);
            return response(['success' => 'SMS sent']);
        } catch (\Exception $e) {
            return response(['success' => $e->getMessage()]);
        }
    }

}
