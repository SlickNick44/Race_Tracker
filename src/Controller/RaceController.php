<?php

namespace App\Controller;

use App\Entity\Race;
use App\Entity\Result;
use App\Form\RaceFormType;
use App\Form\ResultFormType;
use App\Repository\RaceRepository;
use App\Repository\ResultRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use League\Csv\Reader;

class RaceController extends AbstractController
{
    private $em;
    private $raceRepository;

    public function __construct(
        EntityManagerInterface $em,
        RaceRepository $raceRepository,
        ResultRepository $resultRepository
    ) {
        $this->em = $em;
        $this->raceRepository = $raceRepository;
        $this->resultRepository = $resultRepository;
    }

    #[Route('/races', name: 'races')]
    public function index(): Response
    {
        return $this->render('race/index.html.twig', [
            'races' => $this->raceRepository->findAll()
        ]);
    }

    #[Route('/races/create', name: 'create_race')]
    public function create(Request $request): Response
    {
        $race = new Race();
        $form = $this->createForm(RaceFormType::class, $race);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $newRace = $form->getData();

            $filePath = $form->get('filePath')->getData();
            if ($filePath) {
                $newFileName = uniqid() . '.' . $filePath->guessExtension();

                try {
                    $filePath->move(
                        $this->getParameter('kernel.project_dir') . '/public/uploads',
                        $newFileName
                    );
                } catch (FileException $e) {
                    return new Response($e->getMessage());
                }

                $newRace->setFilePath('/uploads/' . $newFileName);
            }

            $this->em->persist($newRace);

            $reader = Reader::createFromPath($this->getParameter('kernel.project_dir') . '/public' . $race->getFilePath());
            $reader->setHeaderOffset(0);

            foreach ($reader->getRecords() as $row) {
                $result = (new Result())
                    ->setFullName($row['fullName'])
                    ->setRaceTime($row['time'])
                    ->setDistance($row['distance'])
                ;

                $this->em->persist($result);

                $result ->setRace($race);
            }

            $this->em->flush();

            return $this->redirectToRoute('races');
        }

        return $this->render('race/create.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/races/edit/{id}', name: 'edit_race')]
    public function edit($id, Request $request): Response
    {
        $race = $this->raceRepository->find($id);
        $form = $this->createForm(RaceFormType::class, $race);

        $form->handleRequest($request);
        $filePath = $form->get('filePath')->getData();

        if ($form->isSubmitted() && $form->isValid()) {
            if ($filePath) {
                if (file_exists($this->getParameter('kernel.project.dir') . $race->getFilePath())) {
                    $this->getParameter('kernel.project.dir') . $race->getFilePath();

                    $newFileName = uniqid() . '.' . $filePath->guessExtension();

                    try {
                        $filePath->move(
                            $this->getParameter('kernel.project_dir') . '/public/uploads',
                            $newFileName
                        );
                    } catch (FileException $e) {
                        return new Response($e->getMessage());
                    }

                    $race->setFilePath('/uploads/' . $newFileName);
                    $this->em->flush();

                    return $this->redirectToRoute('races/$id');
                }
            } else {
                $race->setRaceName($form->get('raceName')->getData());
                $race->setDate($form->get('date')->getData());

                $this->em->flush();

                return $this->redirect('../../races/' . $race->getId());
            }
        }

        return $this->render('race/edit.html.twig', [
            'race' => $race,
            'form' => $form->createView()
        ]);
    }

    #[Route('/races/delete/{id}', methods: ['GET', 'DELETE'], name: 'delete_race')]
    public function delete($id): Response
    {
        $race = $this->raceRepository->find($id);
        $results = $this->resultRepository->findBy(['race' => $id], ['id' => 'DESC']);

        foreach ($results as $result) {
            $this->em->remove($result);
        }

        $this->em->remove($race);
        $this->em->flush();

        return $this->redirectToRoute('races');
    }

    #[Route('/races/{id}', methods: ['GET'], name: 'race' )]
    public function show($id): Response
    {
        return $this->render('race/show.html.twig', [
            'race' => $this->raceRepository->find($id),
            'medium' => $this->resultRepository->findBy(['race' => $id, 'distance' => 'medium'], ['raceTime' => 'ASC']),
            'long' => $this->resultRepository->findBy(['race' => $id, 'distance' => 'long'], ['raceTime' => 'ASC']),
        ]);
    }

    #[Route('/results/edit/{id}', name: 'edit_result')]
    public function result($id, Request $request): Response
    {
        $result = $this->resultRepository->find($id);
        $form = $this->createForm(ResultFormType::class, $result);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $result->setFullName($form->get('fullName')->getData());
            $result->setRaceTime($form->get('raceTime')->getData());
            $result->setDistance($form->get('distance')->getData());
            

            $this->em->flush();

            return $this->redirect('../../races/' . $result->getRace()->getId());
        } else {
                
    }
        return $this->render('race/edit_result.html.twig', [
            'result' => $result,
            'form' => $form->createView()
        ]);
    }
}
