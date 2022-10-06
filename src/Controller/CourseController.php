<?php

namespace App\Controller;

use Pimcore\Controller\FrontendController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

use Pimcore\Model\DataObject;
use Pimcore\Model\DataObject\Booking;
use Pimcore\Model\DataObject\Course;
use Pimcore\Model\DataObject\Course\Listing as CList;
use Pimcore\Model\DataObject\Booking\Listing as BList;

class CourseController extends FrontendController
{

    protected function getAllCourses($courses = null)
    {
        if($courses === null)
        {
            $courses = new CList();
            $courses->load();
        }

        return array_map(fn($c) => [
            'name' => $c->getName(),
            'description' => $c->getDescription(),
            'price' => $c->getPrice(),
            'start_dt' => strval($c->getStart()),
            'end_dt' => strval($c->getEnd()),
            'start_timestamp' => $c->getStart()->timestamp,
            'id' => $c->getId()
        ], ($courses instanceof \Iterator) ? iterator_to_array($courses) : $courses);
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function defaultAction(Request $request): Response
    {
        $opts = [
            'route_name' => 'book_course',
            'button_value' => 'BOOK'
        ];
        
        return $this->render('default/courses.html.twig', ['courses' => $this->getAllCourses(), 'options'=>$opts]);
    }
    /**
    * @param Request $request
    * @return Response
    * @Route("/booked_courses", name="booked_courses", methods = {"GET"})
    * @IsGranted("ROLE_USER")
    */
    
    public function bookedCourses(Request $request): Response
    {
        $bookings = new Blist();
        $bookings->filterByCustomer($this->getUser());
        $bookings->load();

        $courses = array_map(fn($booking) => $booking->getCourse(), iterator_to_array($bookings));

        
        $opts = [
            'route_name' => 'delete_booking',
            'button_value' => 'Delete booking'
        ];

        return $this->render('default/courses.html.twig', ['courses' => $this->getAllCourses($courses), 'options' => $opts]);
    }

   /**
    * @param Request $request
    * @return Response
    * @Route("/book_course", name="book_course", methods = {"POST"})
    * @IsGranted("ROLE_USER")
    */
    public function bookCourse(Request $request): Response
    {

        $entries = new BList();
        $user = $this->getUser();
        $course = Course::getById(intval($request->request->get('course_id')));

        $entries->filterByCourse($course);
        $entries->filterByCustomer($this->getUser());

        $entries->load();

        if(iterator_count($entries) != 0)
        {
            return $this->flashAndRedirect($request, 'error', 'You already booked this course!');
        }

        try
        {
            $booking = new Booking();
            $booking->setParent(DataObject::getByPath('/bookings'));
            $booking->setKey($user->getId() . '_' . $course->getId());
            $booking->setCustomer($user);
            $booking->setCourse($course);
            $booking->setPublished(true);
            $booking->save();

            return $this->flashAndRedirect($request, 'success', 'You successfully booked this course!');
        }
        catch(\Exception $e)
        {
            return $this->flashAndRedirect($request, 'error', $e->getMessage());
        }
    }


    /**
    * @param Request $request
    * @return Response
    * @Route("/delete_booking", name="delete_booking", methods = {"POST"})
    * @IsGranted("ROLE_USER")
    */
    public function deleteBooking(Request $request): Response
    {
        $entries = new BList();
        $user = $this->getUser();
        $course = Course::getById(intval($request->request->get('course_id')));

        $entries->filterByCourse($course);
        $entries->filterByCustomer($this->getUser());

        if(iterator_count($entries) == 0)
        {
            return $this->flashAndRedirect($request, 'error', 'Booking doesn\'t exist!');
        }

        try
        {
            foreach($entries as $b)
                $b->delete();
            return $this->flashAndRedirect($request,'success', 'Successfully deleted booking!');
        }
        catch(\Exception $e)
        {
            return $this->flashAndRedirect($request, 'error', $e->getMessage());
        }
    }

    /**
    * @param Request $request
    * @return Response
    * @Route("/json_courses", name="json_courses", methods = {"GET"})
    */
    public function jsonCourses(Request $request): Response
    {
        return $this->json(['courses' => $this->getAllCourses()]);
    }

    private function flashAndRedirect($request, $fType, $fMsg)
    {
        $this->addFlash($fType, $fMsg);
        $referer = $request->headers->get('referer');   
        return new RedirectResponse($referer);
    }

}
