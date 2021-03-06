<?php

namespace App\Utilities;

use App\Entity\Member;
use App\Entity\Message;
use App\Entity\Subject;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Symfony\Component\Templating\EngineInterface;

trait MessageTrait
{
    use TranslatorTrait;

    /** @var EngineInterface */
    private $engine;

    /**
     * @Required
     *
     * @param EngineInterface $engine
     */
    public function setEngine(EngineInterface $engine)
    {
        $this->engine = $engine;
    }

    protected function getEngine()
    {
        return $this->engine;
    }

    /**
     * @param Member $sender
     * @param Member $receiver
     * @param string $parent
     * @param string $template
     * @param mixed ...$params
     */
    protected function createTemplateMessage(Member $sender, Member $receiver, string $parent, string $template, ...$params)
    {
        $parameters = array_merge(['sender' => $sender, 'receiver' => $receiver, 'template' => $template], ...$params);

        $em = $this->getManager();

        $this->setTranslatorLocale($receiver);
        $subjectText = $this->getTranslator()->trans($parameters['subject']);
        $subject = new Subject();
        $subject->setSubject($subjectText);
        $em->persist($subject);
        $em->flush($subject);

        $message = new Message();
        $message->setSender($sender);
        $message->setReceiver($receiver);
        $message->setSubject($subject);

        $body = $this->getEngine()->render('_partials/'.$parent.'/'.$template.'.html.twig', $parameters);
        $message->setMessage($body);
        $em->persist($message);
        $em->flush();
        $this->setTranslatorLocale($sender);
    }
}
