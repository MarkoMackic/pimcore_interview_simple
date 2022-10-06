<?php

namespace App\Controller;

use Pimcore\Controller\FrontendController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

use Pimcore\Model\DataObject\Customer;
use Pimcore\Model\DataObject;

class RegisterController extends FrontendController
{
    /**
     * @Route("/register", name="register", methods={"GET"})
     */
    public function register_view(Request $req)
    { 
        $fbi = $this->createFormBuilder();

        $fbi
            ->setMethod('PUT')
            ->add('email', TextType::class)
            ->add('first_name', TextType::class)
            ->add('last_name', TextType::class)
            ->add('password', PasswordType::class)
            ->add('confirm_password', PasswordType::class)
            ->add('register', SubmitType::class);

        return $this->renderForm('default/form/form.html.twig', ['form' => $fbi->getForm()]);
    }

    /**
     * @Route("/register", name="do_register", methods={"PUT"})
     */
    public function register(Request $req)
    { 
        $cdata = $req->get('form');

        try
        {
            // params validation for example ..  
            if($cdata['password'] == $cdata['confirm_password'])
            {
                $customer = new Customer();
                $customer->setParent(DataObject::getByPath('/customers'));
                $customer->setKey($cdata['email']);
                $customer->setFirstname($cdata['first_name']);
                $customer->setLastname($cdata['last_name']);
                $customer->setEmail($cdata['email']);
                $customer->setPassword($cdata['password']);
                $customer->setPublished(true);
                $customer->save();

                $this->addFlash('success', "Successfully registered!");
                return $this->redirectToRoute('login');
            }
            else
            {
                $this->addFlash('error', 'Passwords not the same.');
                return $this->redirectToRoute('register');     
            }
        }
        catch(\Exception $e)
        {
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('register');           
        }
    }
}