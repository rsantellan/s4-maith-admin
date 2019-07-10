<?php

namespace Maith\Common\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\Form\Extension\Core\Type\TextType;

class AdminParametersController extends Controller
{

    /**
     * @param Request $request
     * @param \Maith\Common\AdminBundle\Services\MaithParametersService $maithParametersService
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function showParametersAction(Request $request, \Maith\Common\AdminBundle\Services\MaithParametersService $maithParametersService)
    {
        $yamlData = $maithParametersService->getParametersList();
        $formParameters = $this->createFormBuilder();
        $counter = 1;
        $counters = array();
        foreach ($yamlData['parameters'] as $key => $data) {
            $formParameters->add('param_' . $counter, TextType::class, array(
                'data' => $data,
                'label' => $key,
                'required' => true,
            ));
            $counters[$key] = $counter;
            $counter++;
        }
        $realForm = $formParameters->getForm();
        $realForm->handleRequest($request);
        if ($realForm->isSubmitted() && $realForm->isValid()) {
            $formData = $realForm->getData();
            $saveDataArray = array();
            foreach ($counters as $key => $value) {
                $saveDataArray[$key] = $formData['param_' . $value];
            }
            $maithParametersService->saveParameters($saveDataArray);
            return $this->redirect($this->generateUrl('maith_admin_parameters_config'));
            die;
        }
        return $this->render('MaithCommonAdminBundle:AdminParameters:showParameters.html.twig', array(
            'form' => $realForm->createView(),
            'admin_menu' => 'parameters',
            'counters' => $counters,
        ));

        die;
    }
}
