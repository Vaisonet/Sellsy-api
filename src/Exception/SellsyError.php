<?php


namespace SellsyApi\Exception;

use Exception;

class SellsyError extends Exception {

    /**
     * @var mixed
     */
    protected $more;

    /**
     * @var string
     */
    protected $sellsyCode;

    /**
     * SellsyError constructor.
     *
     * @param string    $message
     * @param int       $code
     * @param mixed     $more
     * @param Exception $previous
     */
    public function __construct ($message, $code, $more = NULL, Exception $previous = NULL) {
        parent::__construct($message, 0, $previous);
        $this->more       = $more;
        $this->sellsyCode = $code;
    }

    /**
     * @return string
     */
    public function getSellsyCode () {
        return $this->sellsyCode;
    }

    /**
     * @return mixed
     */
    public function getMore () {
        return $this->more;
    }

}
