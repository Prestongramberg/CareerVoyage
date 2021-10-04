<?php

namespace App\Controller\Api;

use App\Entity\CompanyResource;
use App\Entity\LessonResource;
use App\Entity\Resource;
use App\Entity\User;
use App\Form\ResourceType;
use App\Service\UploaderHelper;
use App\Util\FileHelper;
use App\Util\ServiceHelper;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ResourceController
 *
 * @package App\Controller
 * @Route("/api/resources")
 */
class ResourceController extends AbstractController
{
    use FileHelper;
    use ServiceHelper;


    /**
     * @Route("/new", name="api_resource_new", options = {"expose" = true })
     * @Method({"GET", "POST"})
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function new(Request $request)
    {
        /** @var User $user */
        $user      = $this->getUser();
        $resource  = new Resource();
        $action    = $this->generateUrl('api_resource_new', []);
        $dataClass = Resource::class;

        if ($request->query->has('companyId')) {

            $companyId = $request->query->get('companyId');
            $company   = $this->companyRepository->find($companyId);

            $resource = new CompanyResource();
            $resource->setCompany($company);
            $action    = $this->generateUrl('api_resource_new', [
                'companyId' => $company->getId(),
            ]);
            $dataClass = CompanyResource::class;
        }

        if ($request->query->has('lessonId')) {

            $lessonId = $request->query->get('lessonId');
            $lesson   = $this->lessonRepository->find($lessonId);

            $resource = new LessonResource();
            $resource->setLesson($lesson);
            $action    = $this->generateUrl('api_resource_new', [
                'lessonId' => $lesson->getId(),
            ]);
            $dataClass = LessonResource::class;
        }

        $form = $this->createForm(ResourceType::class, $resource, [
            'action' => $action,
            'data_class' => $dataClass,
            'validation_groups' => $request->request->has('skip_validation') ? [] : ['RESOURCE']
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            /** @var Resource $resource */
            $resource = $form->getData();
            $file = null;
            $folder = UploaderHelper::RESOURCE;
            $url = $resource->getUrl();

            if($form->has('file')) {
                $file = $form->get('file')->getData();
            }

            if ($request->query->has('companyId')) {
                $folder = UploaderHelper::COMPANY_RESOURCE;
            }

            if ($request->query->has('lessonId')) {
                $folder = UploaderHelper::LESSON_RESOURCE;
            }

            if ($file) {
                $mimeType    = $file->getMimeType();
                $newFilename = $this->uploaderHelper->upload($file, $folder);
                $resource->setOriginalName($file->getClientOriginalName() ?? $newFilename);
                $resource->setMimeType($mimeType ?? 'application/octet-stream');
                $resource->setFileName($newFilename);
                $url = '/uploads/' . $folder . '/' . $newFilename;
            }


            $this->entityManager->persist($resource);
            $this->entityManager->flush();

            $editUrl   = $this->generateUrl('api_resource_edit', ['id' => $resource->getId()]);
            $deleteUrl = $this->generateUrl('api_resource_delete', ['id' => $resource->getId()]);

            if ($request->query->has('companyId')) {
                $editUrl = $this->generateUrl('api_resource_edit', ['id' => $resource->getId(),
                                                                 'companyId' => $request->query->get('companyId'),
                ]);

                $deleteUrl = $this->generateUrl('api_resource_delete', ['id' => $resource->getId(),
                                                                     'companyId' => $request->query->get('companyId'),
                ]);
            }

            if ($request->query->has('lessonId')) {
                $editUrl = $this->generateUrl('api_resource_edit', ['id' => $resource->getId(),
                                                                    'lessonId' => $request->query->get('lessonId'),
                ]);

                $deleteUrl = $this->generateUrl('api_resource_delete', ['id' => $resource->getId(),
                                                                        'lessonId' => $request->query->get('lessonId'),
                ]);
            }

            return new JsonResponse(
                [
                    'success' => true,
                    'id' => $resource->getId(),
                    'title' => $resource->getTitle(),
                    'description' => $resource->getDescription(),
                    'editUrl' => $editUrl,
                    'deleteUrl' => $deleteUrl,
                    'resourceUrl' => $url,

                ], Response::HTTP_OK
            );

        }

        $formMarkup = $this->renderView(
            'resource/new.html.twig',
            [
                'form' => $form->createView(),
                'action' => $action
            ]
        );

        return new JsonResponse(
            [
                'formMarkup' => $formMarkup,
            ], Response::HTTP_BAD_REQUEST
        );
    }

    /**
     * @Route("/{id}/edit", name="api_resource_edit", options = {"expose" = true })
     * @Method({"GET", "POST"})
     * @param Request  $request
     *
     * @param Resource $resource
     *
     * @return JsonResponse
     */
    public function edit(Request $request, Resource $resource)
    {
        /** @var User $user */
        $user = $this->getUser();
        $action    = $this->generateUrl('api_resource_edit', ['id' => $resource->getId()]);
        $dataClass = Resource::class;

        if ($request->query->has('companyId')) {
            $action    = $this->generateUrl('api_resource_edit', [
                'id' => $resource->getId(),
                'companyId' => $request->query->get('companyId'),
            ]);
            $dataClass = CompanyResource::class;
        }

        if ($request->query->has('lessonId')) {
            $action    = $this->generateUrl('api_resource_edit', [
                'id' => $resource->getId(),
                'lessonId' => $request->query->get('lessonId'),
            ]);
            $dataClass = LessonResource::class;
        }

        $form = $this->createForm(ResourceType::class, $resource, [
            'action' => $action,
            'data_class' => $dataClass,
            'validation_groups' => $request->request->has('skip_validation') ? [] : ['RESOURCE']
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            /** @var Resource $resource */
            $resource = $form->getData();
            $file = null;
            $folder = UploaderHelper::RESOURCE;
            $url = $resource->getUrl();

            if($form->has('file')) {
                $file = $form->get('file')->getData();
            }

            if ($request->query->has('companyId')) {
                $folder = UploaderHelper::COMPANY_RESOURCE;
            }

            if ($request->query->has('lessonId')) {
                $folder = UploaderHelper::LESSON_RESOURCE;
            }

            if ($file) {
                $mimeType    = $file->getMimeType();
                $newFilename = $this->uploaderHelper->upload($file, $folder);
                $resource->setOriginalName($file->getClientOriginalName() ?? $newFilename);
                $resource->setMimeType($mimeType ?? 'application/octet-stream');
                $resource->setFileName($newFilename);
                $url = '/uploads/' . $folder . '/' . $newFilename;
            }

            $this->entityManager->flush();

            $editUrl   = $this->generateUrl('api_resource_edit', ['id' => $resource->getId()]);
            $deleteUrl = $this->generateUrl('api_resource_delete', ['id' => $resource->getId()]);

            if ($request->query->has('companyId')) {
                $editUrl = $this->generateUrl('api_resource_edit', ['id' => $resource->getId(),
                                                                    'companyId' => $request->query->get('companyId'),
                ]);

                $deleteUrl = $this->generateUrl('api_resource_delete', ['id' => $resource->getId(),
                                                                        'companyId' => $request->query->get('companyId'),
                ]);
            }

            if ($request->query->has('lessonId')) {
                $editUrl = $this->generateUrl('api_resource_edit', ['id' => $resource->getId(),
                                                                    'lessonId' => $request->query->get('lessonId'),
                ]);

                $deleteUrl = $this->generateUrl('api_resource_delete', ['id' => $resource->getId(),
                                                                        'lessonId' => $request->query->get('lessonId'),
                ]);
            }

            return new JsonResponse(
                [
                    'success' => true,
                    'id' => $resource->getId(),
                    'title' => $resource->getTitle(),
                    'description' => $resource->getDescription(),
                    'editUrl' => $editUrl,
                    'deleteUrl' => $deleteUrl,
                    'resourceUrl' => $url,

                ], Response::HTTP_OK
            );
        }

        $formMarkup = $this->renderView(
            'resource/edit.html.twig',
            [
                'form' => $form->createView(),
            ]
        );

        return new JsonResponse(
            [
                'formMarkup' => $formMarkup,
            ], Response::HTTP_BAD_REQUEST
        );
    }

    /**
     * @Route("/{id}/delete", name="api_resource_delete", options = {"expose" = true })
     * @Method({"GET", "POST"})
     * @param Request  $request
     *
     * @param Resource $resource
     *
     * @return JsonResponse
     */
    public function delete(Request $request, Resource $resource)
    {
        /** @var User $user */
        $user = $this->getUser();

        $resourceId = $resource->getId();

        $this->entityManager->remove($resource);
        $this->entityManager->flush();

        return new JsonResponse(
            [
                'success' => true,
                'id' => $resourceId,

            ], Response::HTTP_OK
        );
    }

}