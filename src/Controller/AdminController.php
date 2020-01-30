<?php

namespace App\Controller;

use App\Entity\Items;
use App\Form\ApiFormType;
use App\Service\Helper;
use GuzzleHttp\Client;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
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
        $items = null;
        if ($form->isSubmitted()) {
            $client = new Client([
                // Base URI is used with relative requests
                'base_uri' => 'http://project.loc',
                // You can set any number of default request options.
                'timeout'  => 2.0,
            ]);
            $response = $client->request('POST', '/items');
            $body = $response->getBody();
            $json = $body->getContents();
            $items = json_decode($json, true);
        }
        return $this->render('form.html.twig', [
            'form' => $form->createView(),
            'items' => $items,
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

    /**
     * @Route("/items", name="items", methods={"POST"})
     *
     * @param Request $request
     *
     * @return Response
     */
    public function items(Request $request): Response
    {
        $itemsRepo = $this->getDoctrine()->getRepository(Items::class);
        $items = $itemsRepo->findAll();
        $itemsArr = [];
        foreach ($items as $obj) {
            $item["name"] = $obj->getName();
            $item["qty"] = $obj->getQty();
            array_push($itemsArr, $item);
        }
        $response = new JsonResponse($itemsArr);
        return $response;


    }
}