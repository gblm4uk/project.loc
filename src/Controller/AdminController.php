<?php

namespace App\Controller;

use App\Entity\Items;
use App\Form\ApiFormType;
use App\Service\Helper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends AbstractController
{
    /**
     * @Route("/test", name="test", methods={"POST"})
     *
     * @param Request $request
     *
     * @return Response
     */
    public function test(Request $request): Response
    {
        $itemsRepo = $this->getDoctrine()->getRepository(Items::class);

        $status = 'success';
        $details = '';
        $updateItem = 0;
        $newItem = 0;
        try {
            $json = $request->getContent();
            $data = json_decode($json, true);
            if ($data === null) {
                throw new \Exception('Incorrect JSON');
            }
            $entityManager = $this->getDoctrine()->getManager();
            foreach ($data as $itemArray) {
                foreach (Items::FIELDS as $field) {
                    if (!isset($itemArray[$field])) {
                        throw new \Exception("Field $field not found.");
                    }
                }
                $item = $itemsRepo->findOneBy(['name' => $itemArray[Items::FIELD_ITEM]]);
                if ($item) {
                    $item->setQty($itemArray[Items::FIELD_QTY] + $item->getQty());
                    $updateItem++;
                } else {
                    $item = new Items();
                    $item->setName($itemArray[Items::FIELD_ITEM]);
                    $item->setQty($itemArray[Items::FIELD_QTY]);
                    $newItem++;
                }
                $entityManager->persist($item);
            }
            $entityManager->flush();
            $details = "Created $newItem items, updated $updateItem items";
        } catch (\Exception $e) {
            $status = 'error';
            $details = $e->getMessage();
        }

        return new Response(
            json_encode([
                'status' => $status,
                'details' => $details,
            ])
        );
    }

    /**
     * @Route("/", name="index")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function index(Request $request): Response
    {
        $itemsRepo = $this->getDoctrine()->getRepository(Items::class);
        $items = $itemsRepo->findAll();

        return $this->render('index.html.twig', [
           'items' => $items,
        ]);
    }

    /**
     * @Route("/form", name="form")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function form(Request $request): Response
    {
        $form = $this->createForm(ApiFormType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            // $form->getData() holds the submitted values
            // but, the original `$task` variable has also been updated
            $task = $form->getData();
            dd($task);
            // ... perform some action, such as saving the task to the database
            // for example, if Task is a Doctrine entity, save it!
            // $entityManager = $this->getDoctrine()->getManager();
            // $entityManager->persist($task);
            // $entityManager->flush();

            //return $this->redirectToRoute('task_success');
        }
        return $this->render('form.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/qwe", name="qwe")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function qwe(Request $request): Response
    {
        $helper = new Helper();
        $data = $helper->getFourLetters();

        return $this->render('qwe.html.twig', [
            'data' => $data,
        ]);
    }
}