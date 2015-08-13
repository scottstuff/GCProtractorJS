<?php

namespace GotChosen\SiteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use GotChosen\Mail\Filter;
use GotChosen\Util\Enums;

/**
 * MassMailQueue
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="GotChosen\SiteBundle\Repository\MassMailQueueRepository")
 */
class MassMailQueue
{
    const TYPE_PREVIEW = 1;
    const TYPE_ALL = 2;
    const TYPE_FILTER = 3;

    public static $types = [
        self::TYPE_PREVIEW => 'Preview',
        self::TYPE_ALL => 'All Users',
        self::TYPE_FILTER => 'Custom Filter',
    ];

    const STATUS_NEW = 1;
    const STATUS_PROCESSING = 2;
    const STATUS_COMPLETE = 3;
    const STATUS_ERROR = 4;
    const STATUS_PAUSED = 5;
    const STATUS_RESUMING = 6;

    public static $statuses = [
        self::STATUS_NEW => 'New',
        self::STATUS_PROCESSING => 'Processing',
        self::STATUS_COMPLETE => 'Complete',
        self::STATUS_ERROR => 'Error',
        self::STATUS_PAUSED => 'Paused',
        self::STATUS_RESUMING => 'Resuming',
    ];

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="dateAdded", type="datetime")
     */
    private $dateAdded;

    /**
     * @var integer
     *
     * @ORM\Column(name="type", type="smallint")
     */
    private $type;

    /**
     * @var array
     *
     * @ORM\Column(name="filterSpec", type="json_array")
     */
    private $filterSpec;

    /**
     * @var integer
     *
     * @ORM\Column(name="status", type="smallint")
     */
    private $status;

    /**
     * @var string
     *
     * @ORM\Column(name="errorReason", type="text")
     */
    private $errorReason;

    /**
     * @var integer
     *
     * @ORM\Column(name="position", type="integer")
     */
    private $position;
    
    /**
     * @var integer
     *
     * @ORM\Column(name="sent", type="integer")
     */
    private $sent;

    /**
     * @var integer
     *
     * @ORM\Column(name="total", type="integer")
     */
    private $total;

    /**
     * @var string
     *
     * @ORM\Column(name="template", type="string", length=255)
     */
    private $template;

    /**
     * @var string
     *
     * @ORM\Column(name="subject", type="string", length=255)
     */
    private $subject;

    /**
     * @var array
     *
     * @ORM\Column(name="parameters", type="json_array")
     */
    private $parameters;

    public static function makePreview(User $toUser, $template, $subject, array $params)
    {
        $mq = new MassMailQueue();
        $mq->setType(self::TYPE_PREVIEW);
        $mq->setFilterSpec(['userId' => $toUser->getId()]);
        $mq->setTemplate($template);
        $mq->setSubject($subject);
        $mq->setParameters($params);

        return $mq;
    }

    public static function makeAll($template, $subject, array $params)
    {
        $mq = new MassMailQueue();
        //$mq->setType(self::TYPE_ALL);
        $mq->setType(self::TYPE_FILTER); // filter with empty spec = all
        $mq->setFilterSpec([]);
        $mq->setTemplate($template);
        $mq->setSubject($subject);
        $mq->setParameters($params);

        return $mq;
    }

    public static function makeFiltered(Filter $filter, $template, $subject, array $params)
    {
        $mq = new MassMailQueue();
        $mq->setType(self::TYPE_FILTER);
        $mq->setFilterSpec(Filter::toArray($filter));
        $mq->setTemplate($template);
        $mq->setSubject($subject);
        $mq->setParameters($params);

        return $mq;
    }

    public function __construct()
    {
        $this->dateAdded = new \DateTime();
        $this->status = self::STATUS_NEW;
        $this->errorReason = '';
        $this->position = 0;
        $this->sent = 0;
        $this->total = 0;
    }

    public function getStatusName()
    {
        return self::$statuses[$this->status];
    }

    public function getProgressText()
    {
        if ( $this->status == self::STATUS_NEW || $this->total == 0 ) {
            return '0%';
        }
        if ( $this->status == self::STATUS_COMPLETE ) {
            return '100%';
        }

        return min(100, round(100 * $this->sent / $this->total)) . '%';
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set type
     *
     * @param integer $type
     * @return MassMailQueue
     */
    public function setType($type)
    {
        Enums::assert($type, self::$types);
        $this->type = $type;
    
        return $this;
    }

    /**
     * Get type
     *
     * @return integer
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set filterSpec
     *
     * @param array $filterSpec
     * @return MassMailQueue
     */
    public function setFilterSpec($filterSpec)
    {
        $this->filterSpec = $filterSpec;
    
        return $this;
    }

    /**
     * Get filterSpec
     *
     * @return array
     */
    public function getFilterSpec()
    {
        return $this->filterSpec;
    }

    /**
     * Set status
     *
     * @param integer $status
     * @return MassMailQueue
     */
    public function setStatus($status)
    {
        Enums::assert($status, self::$statuses);
        $this->status = $status;
    
        return $this;
    }

    /**
     * Get status
     *
     * @return integer
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set position
     *
     * @param integer $position
     * @return MassMailQueue
     */
    public function setPosition($position)
    {
        $this->position = $position;
    
        return $this;
    }

    /**
     * Get position
     *
     * @return integer
     */
    public function getPosition()
    {
        return $this->position;
    }
    
    /**
     * Set sent
     * @param integer $sent
     * @return MassMailQueue
     */
    public function setSent($sent)
    {
        $this->sent = $sent;
        
        return $this;
    }
    
    /**
     * Get sent
     *
     * @return integer
     */
    public function getSent()
    {
        return $this->sent;
    }

    /**
     * Set total
     *
     * @param integer $total
     * @return MassMailQueue
     */
    public function setTotal($total)
    {
        $this->total = $total;
    
        return $this;
    }

    /**
     * Get total
     *
     * @return integer
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * Set template
     *
     * @param string $template
     * @return MassMailQueue
     */
    public function setTemplate($template)
    {
        $this->template = $template;
    
        return $this;
    }

    /**
     * Get template
     *
     * @return string
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * Set parameters
     *
     * @param array $parameters
     * @return MassMailQueue
     */
    public function setParameters($parameters)
    {
        $this->parameters = $parameters;
    
        return $this;
    }

    /**
     * Get parameters
     *
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * @return string
     */
    public function getErrorReason()
    {
        return $this->errorReason;
    }

    /**
     * @param string $errorReason
     * @return $this
     */
    public function setErrorReason($errorReason)
    {
        $this->errorReason = $errorReason;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @param mixed $subject
     * @return $this
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDateAdded()
    {
        return $this->dateAdded;
    }

    /**
     * @param \DateTime $dateAdded
     * @return $this
     */
    public function setDateAdded($dateAdded)
    {
        $this->dateAdded = $dateAdded;
        return $this;
    }
}
