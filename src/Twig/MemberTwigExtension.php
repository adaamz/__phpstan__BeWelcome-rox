<?php

namespace App\Twig;

use App\Entity\Activity;
use App\Entity\Comment;
use App\Entity\Group;
use App\Entity\LoginMessage;
use App\Entity\Member;
use App\Entity\Message;
use App\Entity\Notification;
use App\Repository\ActivityRepository;
use App\Repository\CommentRepository;
use App\Repository\LoginMessageRepository;
use App\Repository\MessageRepository;
use App\Repository\NotificationRepository;
use App\Utilities\ManagerTrait;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Router;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Security;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

class MemberTwigExtension extends AbstractExtension implements GlobalsInterface
{
    use ManagerTrait;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var Router
     */
    protected $router;

    /**
     * @var Security
     */
    protected $security;

    /**
     * @var Member
     */
    protected $member;

    /**
     * MemberTwigExtension constructor.
     *
     * @param SessionInterface $session
     * @param RouterInterface  $router
     * @param Security         $security
     */
    public function __construct(
        SessionInterface $session,
        RouterInterface $router,
        Security $security
    ) {
        $this->session = $session;
        $this->router = $router;
        $this->security = $security;
        $this->member = $this->security->getUser();
    }

    /**
     * @return array
     */
    public function getGlobals()
    {
        $teams = null;
        if (null !== $this->member) {
            $roles = $this->security->getUser()->getRoles();
            $teams = $this->getTeams($roles);
        }

        return [
            'loginmessages' => $this->member ? $this->getLoginMessages(): null,
            'groupsInApprovalQueue' => $this->member ? $this->getGroupsInApprovalQueueCount() : null,
            'reportedCommentsCount' => $this->member ? $this->getReportedCommentsCount() : null,
            'reportedMessagesCount' => $this->member ? $this->getReportedMessagesCount() : null,
            'messageCount' => $this->member ? $this->getUnreadMessagesCount() : null,
            'requestCount' => $this->member ? $this->getUnreadRequestsCount() : null,
            'notificationCount' => $this->member ? $this->getUncheckedNotificationsCount() : null,
            'activityCount' => $this->member ? $this->getUpcomingAroundLocationCount() : null,
            'teams' => $teams,
        ];
    }

    public function getName()
    {
        return self::class;
    }

    /**
     * @param array $roles
     *
     * @return array
     */
    protected function getTeams($roles)
    {
        $allTeams = [
            'communitynews' => [
                'trans' => 'AdminCommunityNews',
                'rights' => [Member::ROLE_ADMIN_COMMUNITYNEWS],
                'route' => 'admin_communitynews_overview',
            ],
            'words' => [
                'trans' => 'AdminWord',
                'rights' => [Member::ROLE_ADMIN_WORDS],
                'route' => 'translations',
            ],
            'flags' => [
                'trans' => 'AdminFlags',
                'rights' => [Member::ROLE_ADMIN_FLAGS],
                'route' => 'admin_flags',
            ],
            'rights' => [
                'trans' => 'AdminRights',
                'rights' => [Member::ROLE_ADMIN_RIGHTS],
                'route' => 'admin_rights',
            ],
            'logs' => [
                'trans' => 'AdminLogs',
                'rights' => [Member::ROLE_ADMIN_LOGS],
                'route' => 'admin_logs_overview',
            ],
            'comments' => [
                'trans' => 'AdminComments',
                'rights' => [Member::ROLE_ADMIN_SAFETYTEAM, Member::ROLE_ADMIN_COMMENTS],
                'route' => 'admin_comment_overview',
            ],
            'newmembersbewelcome' => [
                'trans' => 'AdminNewMembers',
                'rights' => [Member::ROLE_ADMIN_SAFETYTEAM, Member::ROLE_ADMIN_NEWMEMBERSBEWELCOME],
                'route' => 'newmembers',
            ],
            'massmail' => [
                'trans' => 'AdminMassMail',
                'rights' => [Member::ROLE_ADMIN_MASSMAIL],
                'route' => 'admin_massmail',
            ],
            'treasurer' => [
                'trans' => 'AdminTreasurer',
                'rights' => [Member::ROLE_ADMIN_TREASURER],
                'route' => 'admin_treasurer_overview',
            ],
            'faq' => [
                'trans' => 'AdminFAQ',
                'rights' => [Member::ROLE_ADMIN_FAQ],
                'route' => 'admin_faqs_overview',
            ],
            'group' => [
                'trans' => 'AdminGroup',
                'rights' => [Member::ROLE_ADMIN_GROUP],
                'route' => 'admin_groups_approval',
                'minimum_level' => 10,
            ],
            'tools' => [
                'trans' => 'AdminVolunteerTools',
                'rights' => [Member::ROLE_ADMIN_SAFETYTEAM, Member::ROLE_ADMIN_ADMIN,
                    Member::ROLE_ADMIN_SQLFORVOLUNTEERS, Member::ROLE_ADMIN_PROFILE,
                    Member::ROLE_ADMIN_CHECKER, Member::ROLE_ADMIN_ACCEPTER,
                ],
                'route' => 'admin_volunteer_tools',
            ],
        ];

        $teams = [];
        $assignedTeams = [];
        foreach ($allTeams as $name => $team) {
            foreach ($roles as $role) {
                if (!\in_array($name, $assignedTeams, true)) {
                    if (\in_array($role->getRole(), $team['rights'], true)) {
                        $add = true;
                        if (isset($team['minimum_level'])) {
                            $level = $this->member->getLevelForRight($role->getRole());
                            if ($level !== $team['minimum_level']) {
                                $add = false;
                            }
                        }
                        if ($add) {
                            $assignedTeams[] = $name;
                            $teams[] = [
                                'trans' => strtolower($team['trans']),
                                'route' => $team['route'],
                            ];
                        }
                    }
                }
            }
        }

        return $teams;
    }

    protected function getGroupsInApprovalQueueCount()
    {
        $groupsInApprovalCount = null;
        if ($this->security->isGranted(Member::ROLE_ADMIN_GROUP)) {
            $level = $this->member->getLevelForRight(Member::ROLE_ADMIN_GROUP);
            if ($level == 10) {
                $groupsRepository = $this->getManager()->getRepository(Group::class);
                $groups = $groupsRepository->findBy([
                    'approved' => Group::OPEN,
                ]);
                $groupsInApprovalCount = \count($groups);
            }
        }

        return $groupsInApprovalCount;
    }

    protected function getReportedCommentsCount()
    {
        $reportedCommentsCount = null;
        if ($this->security->isGranted(Member::ROLE_ADMIN_COMMENTS) ||
                $this->security->isGranted(Member::ROLE_ADMIN_SAFETYTEAM)) {
            /** @var CommentRepository $commentRepository */
            $commentRepository = $this->getManager()->getRepository(Comment::class);
            $reportedCommentsCount = $commentRepository->getReportedCommentsCount();
        }

        return $reportedCommentsCount;
    }

    protected function getReportedMessagesCount()
    {
        $reportedMessagesCount = null;
        if ($this->security->isGranted(Member::ROLE_ADMIN_CHECKER) ||
                $this->security->isGranted(Member::ROLE_ADMIN_SAFETYTEAM)) {
            /** @var MessageRepository $messageRepository */
            $messageRepository = $this->getManager()->getRepository(Message::class);

            $reportedMessagesCount = $messageRepository->getReportedMessagesCount();
        }

        return $reportedMessagesCount;
    }

    protected function getUnreadMessagesCount()
    {
        /** @var MessageRepository $messageRepository */
        $messageRepository = $this->getManager()->getRepository(Message::class);

        return $messageRepository->getUnreadMessagesCount($this->member);
    }

    protected function getUnreadRequestsCount()
    {
        /** @var MessageRepository $messageRepository */
        $messageRepository = $this->getManager()->getRepository(Message::class);

        return $messageRepository->getUnreadRequestsCount($this->member);
    }

    protected function getUncheckedNotificationsCount()
    {
        /** @var NotificationRepository $notificationRepository */
        $notificationRepository = $this->getManager()->getRepository(Notification::class);

        return $notificationRepository->getUncheckedNotificationsCount($this->member);
    }

    /**
     * @return int
     */
    protected function getUpcomingAroundLocationCount()
    {
        /** @var ActivityRepository $activityRepository */
        $activityRepository = $this->getManager()->getRepository(Activity::class);

        return $activityRepository->getUpcomingAroundLocationCount($this->member->getCity());
    }

    /**
     * @return int
     */
    protected function getLoginMessages()
    {
        /** @var LoginMessageRepository $loginMessagsRepository */
        $loginMessageRepository = $this->getManager()->getRepository(LoginMessage::class);

        return $loginMessageRepository->getLoginMessages($this->member);
    }
}
