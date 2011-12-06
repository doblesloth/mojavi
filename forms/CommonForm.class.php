<?php
// +---------------------------------------------------------------------------+
// | This file is part of the ISS package.                                     |
// | Copyright (c) 2006, 2007 Mark Hobson.                                     |
// |                                                                           |
// | For the full copyright and license information, please view the LICENSE   |
// | file that was distributed with this source code. You can also view the    |
// | LICENSE file online at http://www.redfiveconsulting.com                   |
// +---------------------------------------------------------------------------+
/**
 * CommonForm is a pseudo-form.  It doesn't have any code in it, but it does extend another form.  All forms in a 
 * webapp, should extend this form.  If you need to change the inheritance off all the forms in a webapp, then you 
 * can change where this form points to
 */
class CommonForm extends OrmForm {
	
}
?>