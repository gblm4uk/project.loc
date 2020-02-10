<?php

namespace App\Controller;

use App\Entity\Items;
use App\Entity\Post;
use App\Form\ApiFormType;
use App\Form\EditPostType;
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
     * @Route("/", name="blog")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function blog(Request $request): Response
    {
        $post = new Post();
        $form = $this->createForm(EditPostType::class, $post);
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $post = $form->getData();
            $post->setAuthor($this->getUser());
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($post);
            $entityManager->flush();
        }
        $posts = $this->getDoctrine()->getRepository(Post::class)->findAll();
        return $this->render('blog.html.twig', [
            'posts' => $posts,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/edit-post/{id}", name="edit-post")
     *
     * @param Request $request
     * @param int $id
     *
     * @return Response
     */
    public function editPost(Request $request, int $id): Response
    {
        $post = $this->getDoctrine()->getRepository(Post::class)->find($id);
        if ( !$post || $this->getUser()->getEmail() !== $post->getAuthor()->getEmail()) {
            return $this->redirectToRoute('blog');
        }
        $form = $this->createForm(EditPostType::class, $post);
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $post = $form->getData();
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($post);
            $entityManager->flush();
        }

        return $this->render('edit_post.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/delete-post/{id}", name="delete-post")
     *
     * @param Request $request
     * @param int $id
     *
     * @return Response
     */
    public function deletePost(Request $request, int $id): Response
    {
        $post = $this->getDoctrine()->getRepository(Post::class)->find($id);
        if ( !$post || $this->getUser()->getEmail() !== $post->getAuthor()->getEmail()) {
            return $this->redirectToRoute('blog');
        }
        $em = $this->getDoctrine()->getManager();
        $em->remove($post);
        $em->flush();

        return $this->redirectToRoute('blog');
    }

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
     * @Route("/items-list", name="items-list")
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
            $sortType = $form->getData();
            $client = new Client([
                'base_uri' => 'http://project.loc',
                'timeout'  => 5.0,
            ]);
            $response = $client->request('POST', '/items' ,  [
                'json' => $sortType['sort']
            ]);
            $items = json_decode($response->getBody()->getContents(), true);

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
        //$helper = new Helper();
        //$data = $helper->getFourLetters();
        $data = $request->getContent();
        dd($data);

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
        $sortType = json_decode($request->getContent());
        $sortTypeArr = explode('_', $sortType);
        $itemsRepo = $this->getDoctrine()->getRepository(Items::class);
        $items = $itemsRepo->findSorted($sortTypeArr);
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