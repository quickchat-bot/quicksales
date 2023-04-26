<?php

namespace Parser\Library\Protocol;
use Zend\Mail\Protocol\Imap;

class SWIFT_Imap extends Imap
{
	public function search(array $params)
	{
		$response = $this->requestAndResponse('SEARCH', $params);
		if (is_bool($response)) {
			return [];
		}
		
		foreach ($response as $ids) {
			if ($ids[0] == 'SEARCH') {
				array_shift($ids);
				return $ids;
			}
		}
		return [];
	}
}
