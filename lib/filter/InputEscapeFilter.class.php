<?php

class InputEscapeFilter extends sfFilter
{
	protected $replacements = array(
		'<' => '&lt;',
		'>' => '&gt;'
	);

	protected $bannedTags = array('script', 'iframe');


	public function execute($filterChain)
	{
		$holder = $this->getContext()->getRequest()->getParameterHolder();	

		foreach ($holder->getAll() as $key => $value)
		{
			if (preg_match('/_raw$/', $key))
			{
				foreach ($this->bannedTags as $tag)
				{
					$value = preg_replace('#<' . $tag . '.*(</' . $tag . '>|$)#isU', '', $value);
				}

				$holder->set($key, $value);			
			}
			else
			{
				if ($this->getParameter('custom'))
				{
					foreach ($this->replacements as $old => $new)
					{
						$value = str_replace($old, $new, $value);
					}
				}
				else
				{
					$value = htmlspecialchars($value, ENT_NOQUOTES);
				}
	
				$holder->set($key, $value);
			}				
		}
		
		$filterChain->execute();
	}
}