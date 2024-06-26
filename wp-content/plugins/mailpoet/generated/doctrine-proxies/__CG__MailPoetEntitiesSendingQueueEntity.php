<?php

namespace MailPoetDoctrineProxies\__CG__\MailPoet\Entities;

if (!defined('ABSPATH')) exit;



/**
 * DO NOT EDIT THIS FILE - IT WAS CREATED BY DOCTRINE'S PROXY GENERATOR
 */
class SendingQueueEntity extends \MailPoet\Entities\SendingQueueEntity implements \MailPoetVendor\Doctrine\ORM\Proxy\Proxy
{
    /**
     * @var \Closure the callback responsible for loading properties in the proxy object. This callback is called with
     *      three parameters, being respectively the proxy object to be initialized, the method that triggered the
     *      initialization process and an array of ordered parameters that were passed to that method.
     *
     * @see \Doctrine\Common\Proxy\Proxy::__setInitializer
     */
    public $__initializer__;

    /**
     * @var \Closure the callback responsible of loading properties that need to be copied in the cloned object
     *
     * @see \Doctrine\Common\Proxy\Proxy::__setCloner
     */
    public $__cloner__;

    /**
     * @var boolean flag indicating if this object was already initialized
     *
     * @see \Doctrine\Persistence\Proxy::__isInitialized
     */
    public $__isInitialized__ = false;

    /**
     * @var array<string, null> properties to be lazy loaded, indexed by property name
     */
    public static $lazyPropertiesNames = array (
);

    /**
     * @var array<string, mixed> default values of properties to be lazy loaded, with keys being the property names
     *
     * @see \Doctrine\Common\Proxy\Proxy::__getLazyProperties
     */
    public static $lazyPropertiesDefaults = array (
);



    public function __construct(?\Closure $initializer = null, ?\Closure $cloner = null)
    {

        $this->__initializer__ = $initializer;
        $this->__cloner__      = $cloner;
    }

    /**
     * {@inheritDoc}
     * @param string $name
     */
    public function __get($name)
    {
        $this->__initializer__ && $this->__initializer__->__invoke($this, '__get', [$name]);
        return parent::__get($name);
    }





    /**
     * 
     * @return array
     */
    public function __sleep()
    {
        if ($this->__isInitialized__) {
            return ['__isInitialized__', '' . "\0" . 'MailPoet\\Entities\\SendingQueueEntity' . "\0" . 'newsletterRenderedBody', '' . "\0" . 'MailPoet\\Entities\\SendingQueueEntity' . "\0" . 'newsletterRenderedSubject', '' . "\0" . 'MailPoet\\Entities\\SendingQueueEntity' . "\0" . 'countTotal', '' . "\0" . 'MailPoet\\Entities\\SendingQueueEntity' . "\0" . 'countProcessed', '' . "\0" . 'MailPoet\\Entities\\SendingQueueEntity' . "\0" . 'countToProcess', '' . "\0" . 'MailPoet\\Entities\\SendingQueueEntity' . "\0" . 'meta', '' . "\0" . 'MailPoet\\Entities\\SendingQueueEntity' . "\0" . 'task', '' . "\0" . 'MailPoet\\Entities\\SendingQueueEntity' . "\0" . 'newsletter', '' . "\0" . 'MailPoet\\Entities\\SendingQueueEntity' . "\0" . 'id', '' . "\0" . 'MailPoet\\Entities\\SendingQueueEntity' . "\0" . 'createdAt', '' . "\0" . 'MailPoet\\Entities\\SendingQueueEntity' . "\0" . 'updatedAt', '' . "\0" . 'MailPoet\\Entities\\SendingQueueEntity' . "\0" . 'deletedAt'];
        }

        return ['__isInitialized__', '' . "\0" . 'MailPoet\\Entities\\SendingQueueEntity' . "\0" . 'newsletterRenderedBody', '' . "\0" . 'MailPoet\\Entities\\SendingQueueEntity' . "\0" . 'newsletterRenderedSubject', '' . "\0" . 'MailPoet\\Entities\\SendingQueueEntity' . "\0" . 'countTotal', '' . "\0" . 'MailPoet\\Entities\\SendingQueueEntity' . "\0" . 'countProcessed', '' . "\0" . 'MailPoet\\Entities\\SendingQueueEntity' . "\0" . 'countToProcess', '' . "\0" . 'MailPoet\\Entities\\SendingQueueEntity' . "\0" . 'meta', '' . "\0" . 'MailPoet\\Entities\\SendingQueueEntity' . "\0" . 'task', '' . "\0" . 'MailPoet\\Entities\\SendingQueueEntity' . "\0" . 'newsletter', '' . "\0" . 'MailPoet\\Entities\\SendingQueueEntity' . "\0" . 'id', '' . "\0" . 'MailPoet\\Entities\\SendingQueueEntity' . "\0" . 'createdAt', '' . "\0" . 'MailPoet\\Entities\\SendingQueueEntity' . "\0" . 'updatedAt', '' . "\0" . 'MailPoet\\Entities\\SendingQueueEntity' . "\0" . 'deletedAt'];
    }

    /**
     * 
     */
    public function __wakeup()
    {
        if ( ! $this->__isInitialized__) {
            $this->__initializer__ = function (SendingQueueEntity $proxy) {
                $proxy->__setInitializer(null);
                $proxy->__setCloner(null);

                $existingProperties = get_object_vars($proxy);

                foreach ($proxy::$lazyPropertiesDefaults as $property => $defaultValue) {
                    if ( ! array_key_exists($property, $existingProperties)) {
                        $proxy->$property = $defaultValue;
                    }
                }
            };

        }
    }

    /**
     * 
     */
    public function __clone()
    {
        $this->__cloner__ && $this->__cloner__->__invoke($this, '__clone', []);
    }

    /**
     * Forces initialization of the proxy
     */
    public function __load(): void
    {
        $this->__initializer__ && $this->__initializer__->__invoke($this, '__load', []);
    }

    /**
     * {@inheritDoc}
     * @internal generated method: use only when explicitly handling proxy specific loading logic
     */
    public function __isInitialized(): bool
    {
        return $this->__isInitialized__;
    }

    /**
     * {@inheritDoc}
     * @internal generated method: use only when explicitly handling proxy specific loading logic
     */
    public function __setInitialized($initialized): void
    {
        $this->__isInitialized__ = $initialized;
    }

    /**
     * {@inheritDoc}
     * @internal generated method: use only when explicitly handling proxy specific loading logic
     */
    public function __setInitializer(?\Closure $initializer = null): void
    {
        $this->__initializer__ = $initializer;
    }

    /**
     * {@inheritDoc}
     * @internal generated method: use only when explicitly handling proxy specific loading logic
     */
    public function __getInitializer(): ?\Closure
    {
        return $this->__initializer__;
    }

    /**
     * {@inheritDoc}
     * @internal generated method: use only when explicitly handling proxy specific loading logic
     */
    public function __setCloner(?\Closure $cloner = null): void
    {
        $this->__cloner__ = $cloner;
    }

    /**
     * {@inheritDoc}
     * @internal generated method: use only when explicitly handling proxy specific cloning logic
     */
    public function __getCloner(): ?\Closure
    {
        return $this->__cloner__;
    }

    /**
     * {@inheritDoc}
     * @internal generated method: use only when explicitly handling proxy specific loading logic
     * @deprecated no longer in use - generated code now relies on internal components rather than generated public API
     * @static
     */
    public function __getLazyProperties(): array
    {
        return self::$lazyPropertiesDefaults;
    }

    
    /**
     * {@inheritDoc}
     */
    public function getNewsletterRenderedBody()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getNewsletterRenderedBody', []);

        return parent::getNewsletterRenderedBody();
    }

    /**
     * {@inheritDoc}
     */
    public function setNewsletterRenderedBody($newsletterRenderedBody)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setNewsletterRenderedBody', [$newsletterRenderedBody]);

        return parent::setNewsletterRenderedBody($newsletterRenderedBody);
    }

    /**
     * {@inheritDoc}
     */
    public function getNewsletterRenderedSubject()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getNewsletterRenderedSubject', []);

        return parent::getNewsletterRenderedSubject();
    }

    /**
     * {@inheritDoc}
     */
    public function setNewsletterRenderedSubject($newsletterRenderedSubject)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setNewsletterRenderedSubject', [$newsletterRenderedSubject]);

        return parent::setNewsletterRenderedSubject($newsletterRenderedSubject);
    }

    /**
     * {@inheritDoc}
     */
    public function getCountTotal()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getCountTotal', []);

        return parent::getCountTotal();
    }

    /**
     * {@inheritDoc}
     */
    public function setCountTotal($countTotal)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setCountTotal', [$countTotal]);

        return parent::setCountTotal($countTotal);
    }

    /**
     * {@inheritDoc}
     */
    public function getCountProcessed()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getCountProcessed', []);

        return parent::getCountProcessed();
    }

    /**
     * {@inheritDoc}
     */
    public function setCountProcessed($countProcessed)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setCountProcessed', [$countProcessed]);

        return parent::setCountProcessed($countProcessed);
    }

    /**
     * {@inheritDoc}
     */
    public function getCountToProcess()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getCountToProcess', []);

        return parent::getCountToProcess();
    }

    /**
     * {@inheritDoc}
     */
    public function setCountToProcess($countToProcess)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setCountToProcess', [$countToProcess]);

        return parent::setCountToProcess($countToProcess);
    }

    /**
     * {@inheritDoc}
     */
    public function getMeta()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getMeta', []);

        return parent::getMeta();
    }

    /**
     * {@inheritDoc}
     */
    public function setMeta($meta)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setMeta', [$meta]);

        return parent::setMeta($meta);
    }

    /**
     * {@inheritDoc}
     */
    public function getTask()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getTask', []);

        return parent::getTask();
    }

    /**
     * {@inheritDoc}
     */
    public function setTask(\MailPoet\Entities\ScheduledTaskEntity $task)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setTask', [$task]);

        return parent::setTask($task);
    }

    /**
     * {@inheritDoc}
     */
    public function getNewsletter()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getNewsletter', []);

        return parent::getNewsletter();
    }

    /**
     * {@inheritDoc}
     */
    public function setNewsletter(\MailPoet\Entities\NewsletterEntity $newsletter)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setNewsletter', [$newsletter]);

        return parent::setNewsletter($newsletter);
    }

    /**
     * {@inheritDoc}
     */
    public function getId()
    {
        if ($this->__isInitialized__ === false) {
            return (int)  parent::getId();
        }


        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getId', []);

        return parent::getId();
    }

    /**
     * {@inheritDoc}
     */
    public function setId($id)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setId', [$id]);

        return parent::setId($id);
    }

    /**
     * {@inheritDoc}
     */
    public function getCreatedAt(): ?\DateTimeInterface
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getCreatedAt', []);

        return parent::getCreatedAt();
    }

    /**
     * {@inheritDoc}
     */
    public function setCreatedAt(\DateTimeInterface $createdAt): void
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setCreatedAt', [$createdAt]);

        parent::setCreatedAt($createdAt);
    }

    /**
     * {@inheritDoc}
     */
    public function getUpdatedAt()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getUpdatedAt', []);

        return parent::getUpdatedAt();
    }

    /**
     * {@inheritDoc}
     */
    public function setUpdatedAt(\DateTimeInterface $updatedAt)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setUpdatedAt', [$updatedAt]);

        return parent::setUpdatedAt($updatedAt);
    }

    /**
     * {@inheritDoc}
     */
    public function getDeletedAt()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getDeletedAt', []);

        return parent::getDeletedAt();
    }

    /**
     * {@inheritDoc}
     */
    public function setDeletedAt($deletedAt)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setDeletedAt', [$deletedAt]);

        return parent::setDeletedAt($deletedAt);
    }

}
