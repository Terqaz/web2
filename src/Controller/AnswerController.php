<?php

namespace App\Controller;

use \DateTime;
use App\Entity\Answer;
use App\Form\AnswerType;
use App\Repository\AnswerRepository;
use App\Repository\QuestionRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

#[Route('/answers')]
class AnswerController extends AbstractController
{
    // #[Route('/', name: 'app_answer_index', methods: ['GET'])]
    // public function index(AnswerRepository $answerRepository): Response
    // {
    //     return $this->render('answer/index.html.twig', [
    //         'answers' => $answerRepository->findAll(),
    //     ]);
    // }

    private $security;

    public function __construct(Security $security)
    {
        // Avoid calling getUser() in the constructor: auth may not
        // be complete yet. Instead, store the entire Security object.
        $this->security = $security;
    }

    #[Route('/new', name: 'app_answer_new', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function new(Request $request, 
                        FormFactoryInterface $formFactory,
                        QuestionRepository $questionRepository, 
                        AnswerRepository $answerRepository,
                        UserRepository $userRepository
    ): Response
    {
        $questionId = (int) $request->query->get('question_id');

        $answer = new Answer();
        if ($questionId > 0) {
            $answer->setQuestion($questionRepository->find($questionId));
        }
        $form = $formFactory->createNamed(
            '', 
            AnswerType::class, 
            $answer,
            [
                'questionId' => $questionId
            ]
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $questionId = $form['questionId']->getData();
            $answer
                ->setQuestion($questionRepository->find($questionId))
                ->setIsModerated($this->isGranted('ROLE_ADMIN'))
                ->setDateCreated(new DateTime())
                ->setAuthor($this->security->getUser());

            $answerRepository->add($answer);

            return $this->redirectToRoute(
                'app_question_show', 
                ['id' => $questionId], 
                Response::HTTP_SEE_OTHER
            );
        }

        return $this->renderForm('answer/_form.html.twig', [
            'answer' => $answer,
            'form' => $form,
        ]);
    }

    // #[Route('/{id}', name: 'app_answer_show', methods: ['GET'])]
    // public function show(Answer $answer): Response
    // {
    //     return $this->render('answer/show.html.twig', [
    //         'answer' => $answer,
    //     ]);
    // }

    #[Route('/{id}/edit', name: 'app_answer_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function edit(Request $request, Answer $answer, AnswerRepository $answerRepository): Response
    {
        $form = $this->createForm(AnswerType::class, $answer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $answerRepository->add($answer);
            return $this->redirectToRoute('app_answer_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('answer/edit.html.twig', [
            'answer' => $answer,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_answer_delete', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(Request $request, Answer $answer, AnswerRepository $answerRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$answer->getId(), $request->request->get('_token'))) {
            $answerRepository->remove($answer);
        }

        return $this->redirectToRoute('app_answer_index', [], Response::HTTP_SEE_OTHER);
    }
}
