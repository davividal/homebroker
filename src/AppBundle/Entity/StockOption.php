<?php

namespace AppBundle\Entity;

/**
 * StockOption
 */
class StockOption
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $company;

    /**
     * @var string
     */
    private $ticker_symbol;

    /**
     * @var integer
     */
    private $quantity;

    /**
     * @var string
     */
    private $value;


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
     * Set company
     *
     * @param string $company
     *
     * @return StockOption
     */
    public function setCompany($company)
    {
        $this->company = $company;

        return $this;
    }

    /**
     * Get company
     *
     * @return string
     */
    public function getCompany()
    {
        return $this->company;
    }

    /**
     * Set tickerSymbol
     *
     * @param string $tickerSymbol
     *
     * @return StockOption
     */
    public function setTickerSymbol($tickerSymbol)
    {
        $this->ticker_symbol = $tickerSymbol;

        return $this;
    }

    /**
     * Get tickerSymbol
     *
     * @return string
     */
    public function getTickerSymbol()
    {
        return $this->ticker_symbol;
    }

    /**
     * Set quantity
     *
     * @param integer $quantity
     *
     * @return StockOption
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;

        return $this;
    }

    /**
     * Get quantity
     *
     * @return integer
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * Set value
     *
     * @param string $value
     *
     * @return StockOption
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }
}

