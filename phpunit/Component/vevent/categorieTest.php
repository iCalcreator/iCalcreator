<?php
class categorieTest extends calendarComponent_TestCase
{
	/**
	 * @dataProvider categoriesProvider
	 */
	public function testWithoutParams($categories)
	{											
		$comp = new vevent();
		$comp->setProperty( 'Categories', $categories );
		$expected = 'CATEGORIES:'.$this->prepareCategories($categories);
		$actual = $comp->createCategories();
		$this->assertStringEquals($expected, $actual);
	}
	
	/**
	 * @dataProvider categoriesProvider
	 */
	public function testWithParams($categories)
	{											
		$comp = new vevent();
		$comp->setProperty( 'Categories', $categories, array('hejsan' => 'tjoflojt', '1-param', '2-param', 'language' => 'en' ) );
		$expected = 'CATEGORIES;LANGUAGE=en;1-param;2-param;HEJSAN=tjoflojt:'.$this->prepareCategories($categories,', ');
		//@TODO calendarComponent inserts a space after 73 - 77 characters. Why?
		$expected = str_replace(' ', '', $expected);
		$actual = str_replace(' ','',$comp->createCategories());
		$this->assertStringEquals($expected, $actual);
	}
	
	public function categoriesProvider()
	{
		return array(
			array('Ficklampa'),
			array('Ficklampa, hammare, skruvmejsel'),
			array(array('Ficklampa', 'hammare', 'skruvmejsel')),
			array(array('Ficklampa, hammare', 'skruvmejsel')),
		);
	}
	
	/**
	 * create a valid category string from passed categories.
	 * 
	 * @TODO different glues for arrays are used depending on the existence of 
	 * passed Parameters. Why?
	 * 
	 * @TODO commas passed as string are escaped, not so on concat of arrays.
	 * 
	 * @param mixed $categories string or array of strings
	 * @param atring $glue concat of array elements
	 * @return type
	 */
	public function prepareCategories( $categories, $glue=',' )
	{
		$categories = str_replace(',','\,',$categories);
		if( is_array($categories) )
		{
			$categories = implode($glue, $categories);
		}
		return $categories;
	}
}