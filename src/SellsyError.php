<?php


class SellsyError extends Exception {

    /**
     * @var mixed
     */
    protected $more;

    /**
     * SellsyError constructor.
     *
     * @param string    $message
     * @param int       $code
     * @param mixed     $more
     * @param Exception $previous
     */
    public function __construct ($message, $code, $more = NULL, Exception $previous = NULL) {
        parent::__construct($message, $code, $previous);
        $this->more = $more;
    }

    /**
     * @return mixed
     */
    public function getMore () {
        return $this->more;
    }

}