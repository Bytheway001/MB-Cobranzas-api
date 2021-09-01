<?php
namespace App\Operations;

interface IOperation
{
    /* We must build the initial data */
    public function __construct();

    /* We must run through the steps */
    public function process();
    /* We must obtain the right data */
    public function validateRequest();
    /* We must set a response */
    public function prepareResponse();
}
