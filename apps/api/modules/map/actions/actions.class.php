<?php

class mapActions extends LsApiActions
{
  public function executeEntities($request)
  {
    $this->setResponseFormat();    

    $entity_ids = explode(",", $request->getParameter('entity_ids'));
    $this->data = EntityTable::getEntitiesAndRelsForMap($entity_ids);

    if ($request->getParameter('format') == "json")
    {
      return $this->renderText(json_encode($this->data));
    }
    else
    {
      return 'Xml';
    }
  }
}