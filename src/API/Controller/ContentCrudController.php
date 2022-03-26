<?php

namespace App\API\Controller;

use App\API\Entity\AbstractContent;
use App\API\Repository\AbstractContentRepository;
use App\API\Utility\ContentFactory;
use App\API\Utility\HalJsonFactory;
use App\API\Utility\ImageUploadService;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ContentCrudController extends AbstractController
{
    private $hjf;
    private $cf;
    private $vi;
    private $em;
    private $ius;

    public function __construct(
        HalJsonFactory $hjf,
        ContentFactory $cf,
        ValidatorInterface $vi,
        ManagerRegistry $doctrine,
        ImageUploadService $ius
    ) {
        $this->hjf = $hjf;
        $this->cf = $cf;
        $this->vi = $vi;
        $this->em = $doctrine->getManager();
        $this->ius = $ius;
    }

    public function collection(
        Request $req,
        AbstractContentRepository $repo,
        string $self,
        string $onCreate,
        ?string $mediaType = null
    ): Response {
        switch ($req->getMethod()) {
            case 'GET':
                return $this->readAll($req, $repo, $self);
                break;
            case 'POST':
                return $this->create($req, $mediaType, $onCreate);
        }

        throw new MethodNotAllowedHttpException(
            ['GET', 'POST'],
            $req->getMethod() . ' is not allowed at this endpoint.'
        );
    }

    public function singleton(Request $req, string $uuid, AbstractContentRepository $repo): Response
    {
        switch ($req->getMethod()) {
            case 'GET':
                return $this->read($repo, $uuid);
                break;
            case 'PUT':
            case 'PATCH':
                return $this->update($req, $repo, $uuid);
                break;
            case 'DELETE':
                return $this->delete($repo, $uuid);
        }

        throw new MethodNotAllowedHttpException(['GET', 'PUT', 'PATCH', 'DELETE'], $req->getMethod() . ' is not allowed at this endpoint.');
    }

    public function readAll(Request $req, AbstractContentRepository $repo, string $self = ''): Response
    {
        $tag = $req->query->get('tag');
        $content = $tag ? $repo->findAllWithTag($tag) : $repo->findAll();
        $collection = $this->hjf->createCollection('media', $content);
        if ($self) {
            $collection->link('self', $this->generateUrl($self));
        }

        return $this->json($collection);
    }

    public function create(Request $req, ?string $mediaType = null, string $onCreate = ''): Response
    {
        $content = $this->cf->createFromArray($req->request->all(), $mediaType);
        $errors = $this->vi->validate($content);
        if (count($errors) > 0) {
            throw new ValidationFailedException('value goes here?', $errors);
        }

        $this->saveThumbnail($content, $req->files->get('thumbnail'));

        $this->em->persist($content);
        $this->em->flush();

        return $this->redirectToRoute($onCreate, ['uuid' => $content->getUuid()], 201);
    }

    public function read(AbstractContentRepository $repo, string $uuid): Response
    {
        $content = $repo->findOneBy(['uuid' => $uuid]);
        if (!$content) {
            throw $this->createNotFoundException('Resource not found.');
        }
        $json = $this->hjf->create($content);

        return $this->json($json);
    }

    public function update(Request $request, AbstractContentRepository $repo, string $uuid): Response
    {
        $content = $repo->findOneBy(['uuid' => $uuid]);
        if (!$content) {
            throw $this->createNotFoundException('Resource not found.');
        }

        $content->setByArray($request->request->all());

        $errors = $this->vi->validate($content);
        if (count($errors) > 0) {
            throw new ValidationFailedException('value goes here?', $errors);
        }

        $this->saveThumbnail($content, $req->files->get('thumbnail'));

        $this->em->persist($content);
        $this->em->flush();

        return new Response('', 204);
    }

    public function delete(AbstractContentRepository $repo, string $uuid): Response
    {
        $content = $repo->findOneBy(['uuid' => $uuid]);
        if (!$content) {
            throw $this->createNotFoundException('Resource not found.');
        }

        $this->em->remove($content);
        $this->em->flush();

        return new Response('', 204);
    }

    private function saveThumbnail(AbstractContent &$content, UploadedFile $thumbnail): void
    {
        if ($thumbnail && $thumbnail->isValid() && 'image/jpeg' === $thumbnail->getMimeType()) {
            $filename = $this->ius->uploadImage($thumbnail);
            $content->setThumbnail($filename);
        }
    }
}
