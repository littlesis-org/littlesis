<?php

class addressActions extends sfActions
{
  public function executeView($request)
  {
    $this->redirect('entity/address?id=' . $request->getParameter('id'));  
  }
}