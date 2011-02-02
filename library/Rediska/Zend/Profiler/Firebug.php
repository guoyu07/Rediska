<?php

class Rediska_Zend_Profiler_Firebug extends Rediska_Profiler
{
    protected $_message;

    protected $_label = "Rediska '%rediskaName%' instance commands: %count% in %totalElapsedTime% sec";

    /**
     * Constructor
     *
     * @param array $options
     */
    public function __construct($options = array())
    {
        if (isset($options['label'])) {
            $this->setLabel($options['label']);
        }
    }

    public function setLabel($label)
    {
        $this->_label = $label;

        return $this;
    }

    public function getLabel()
    {
        return $this->_label;
    }

    public function getMessage()
    {
        if (!$this->_message) {
             $message = new Zend_Wildfire_Plugin_FirePhp_TableMessage('');
             $message->setBuffered(true);
             $message->setHeader(array('Time', 'Command', 'Arguments'));
             $message->setDestroy(false);
             $message->setOption('includeLineNumbers', false);

             Zend_Wildfire_Plugin_FirePhp::getInstance()->send($message);

             $this->_message = $message;
        }

        return $this->_message;
    }

    /**
     * Stop callback. Called from profile
     *
     * @param Rediska_Profiler_Profile $profile
     */
    public function stopCallback(Rediska_Profiler_Profile $profile)
    {
        $commandString = $profile->getContext()->__toString();

        $matches = array();
        preg_match('/^(.+)\((.*)\)$/', $commandString, $matches);

        $this->getMessage()->addRow(array(
            $profile->getElapsedTime(4),
            $matches[1],
            $matches[2]
        ));

        $placeHolders = array(
            '%rediskaName%'      => $profile->getContext()->getRediska()->getName(),
            '%count%'            => $this->count(),
            '%totalElapsedTime%' => $this->getTotalElapsedTime(4),
        );

        $label = str_replace(
            array_keys($placeHolders),
            array_values($placeHolders),
            $this->getLabel()
        );

        $this->getMessage()->setLabel($label);
    }
}