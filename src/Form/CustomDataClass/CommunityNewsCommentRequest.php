<?php

namespace App\Form\CustomDataClass;

use App\Entity\CommunityNews;
use App\Entity\CommunityNewsComment;
use Symfony\Component\Validator\Constraints as Assert;

class CommunityNewsCommentRequest
{
    /**
     * @var CommunityNews
     */
    public $communityNews;

    /**
     * @var string
     * @Assert\NotBlank()
     * @Assert\Length(min="10", max="100")
     */
    public $title;

    /**
     * @var string
     * @Assert\NotBlank()
     */
    public $text;

    public static function fromCommunityNewsComment(CommunityNewsComment $communityNewsComment): self
    {
        $communityNewsCommentRequest = new self();
        $communityNewsCommentRequest->communityNews = $communityNewsComment->getCommunityNews();
        $communityNewsCommentRequest->title = $communityNewsComment->getTitle();
        $communityNewsCommentRequest->text = $communityNewsComment->getText();

        return $communityNewsCommentRequest;
    }
}
