﻿..  Content substitution
	...................................................
	Hint: following expression |my_substition_value| will be replaced when rendering doc.

.. |author| replace:: Dominic Garms <djgarms@gmail.com>
.. |extension_key| replace:: flux_galleria
.. |extension_name| replace:: Galleria image gallery
.. |typo3| image:: Images/Typo3.png
.. |time| date:: %m-%d-%Y %H:%M

..  Custom roles
	...................................................
	After declaring a role like this: ".. role:: custom", the document may use the new role like :custom:`interpreted text`. 
	Basically, this will wrap the content with a CSS class to be styled in a special way when document get rendered.
	More information: http://docutils.sourceforge.net/docs/ref/rst/roles.html

.. role:: code
.. role:: typoscript
.. role:: typoscript(code)
.. role:: ts(typoscript)
.. role:: php(code)
