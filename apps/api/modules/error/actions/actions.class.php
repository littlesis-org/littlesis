<?php

class errorActions extends LsApiActions
{
  public function execute404($reuqest)
  {
    $this->returnStatusCode(404);
  }
}