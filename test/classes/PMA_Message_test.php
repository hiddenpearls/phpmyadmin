<?php
/* vim: set expandtab sw=4 ts=4 sts=4: */
/**
 * Test for PMA_Message class
 *
 * @package PhpMyAdmin-test
 */

/*
 * Include to test.
 */
require_once 'libraries/sanitizing.lib.php';
require_once 'libraries/core.lib.php';
require_once 'libraries/Message.class.php';
require_once 'libraries/php-gettext/gettext.inc';

class PMA_Message_test extends PHPUnit_Framework_TestCase
{
    /**
     * @var    PMA_Message
     * @access protected
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     *
     * @access protected
     */
    protected function setUp()
    {
        $this->object = new PMA_Message;
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     *
     * @access protected
     */
    protected function tearDown()
    {
    }

    /**
     * to String casting test
     */
    public function test__toString()
    {
        $this->object->setMessage('test<&>', true);
        $this->assertEquals('test&lt;&amp;&gt;', (string)$this->object);
    }

    /**
     * test success method
     */
    public function testSuccess()
    {
        $this->object = new PMA_Message('test<&>', PMA_Message::SUCCESS);
        $this->assertEquals($this->object, PMA_Message::success('test<&>'));
        $this->assertEquals('Your SQL query has been executed successfully', PMA_Message::success()->getString());
    }

    /**
     * test error method
     */
    public function testError()
    {
        $this->object = new PMA_Message('test<&>', PMA_Message::ERROR);
        $this->assertEquals($this->object, PMA_Message::error('test<&>'));
        $this->assertEquals('Error', PMA_Message::error()->getString());
    }

    /**
     * test notice method
     */
    public function testNotice()
    {
        $this->object = new PMA_Message('test<&>', PMA_Message::NOTICE);
        $this->assertEquals($this->object, PMA_Message::notice('test<&>'));
    }

    /**
     * test rawError method
     */
    public function testRawError()
    {
        $this->object = new PMA_Message('', PMA_Message::ERROR);
        $this->object->setMessage('test<&>');

        $this->assertEquals($this->object, PMA_Message::rawError('test<&>'));
    }

    /**
     * test rawNotice method
     */
    public function testRawNotice()
    {
        $this->object = new PMA_Message('', PMA_Message::NOTICE);
        $this->object->setMessage('test<&>');

        $this->assertEquals($this->object, PMA_Message::rawNotice('test<&>'));
    }

    /**
     * test rawSuccess method
     */
    public function testRawSuccess()
    {
        $this->object = new PMA_Message('', PMA_Message::SUCCESS);
        $this->object->setMessage('test<&>');

        $this->assertEquals($this->object, PMA_Message::rawSuccess('test<&>'));
    }

    /**
     * testing isSuccess method
     */
    public function testIsSuccess()
    {
        $this->assertFalse($this->object->isSuccess());
        $this->assertTrue($this->object->isSuccess(true));
    }

    /**
     * testing isNotice method
     */
    public function testIsNotice()
    {
        $this->assertTrue($this->object->isNotice());
        $this->object->isError(true);
        $this->assertFalse($this->object->isNotice());
        $this->assertTrue($this->object->isNotice(true));
    }

    /**
     * testing isError method
     */
    public function testIsError()
    {
        $this->assertFalse($this->object->isError());
        $this->assertTrue($this->object->isError(true));
    }

    /**
     * testign setter of message
     */
    public function testSetMessage()
    {
        $this->object->setMessage('test&<>', false);
        $this->assertEquals('test&<>', $this->object->getMessage());
        $this->object->setMessage('test&<>', true);
        $this->assertEquals('test&amp;&lt;&gt;', $this->object->getMessage());
    }

    /**
     * testing setter of string
     */
    public function testSetString()
    {
        $this->object->setString('test&<>', false);
        $this->assertEquals('test&<>', $this->object->getString());
        $this->object->setString('test&<>', true);
        $this->assertEquals('test&amp;&lt;&gt;', $this->object->getString());
    }

    /**
     * testing add param method
     */
    public function testAddParam()
    {
        $this->object->addParam(PMA_Message::notice('test'));
        $this->assertEquals(array(PMA_Message::notice('test')), $this->object->getParams());
        $this->object->addParam('test', true);
        $this->assertEquals(array(PMA_Message::notice('test'), 'test'), $this->object->getParams());
        $this->object->addParam('test', false);
        $this->assertEquals(array(PMA_Message::notice('test'), 'test', PMA_Message::notice('test')), $this->object->getParams());
    }

    /**
     * testing add string method
     */
    public function testAddString()
    {
        $this->object->addString('test', '*');
        $this->assertEquals(array('*', PMA_Message::notice('test')), $this->object->getAddedMessages());
        $this->object->addString('test', '');
        $this->assertEquals(array('*', PMA_Message::notice('test'), '', PMA_Message::notice('test')), $this->object->getAddedMessages());
    }

    /**
     * testing add message method
     */
    public function testAddMessage()
    {
        $this->object->addMessage('test', '');
        $this->assertEquals(array(PMA_Message::rawNotice('test')), $this->object->getAddedMessages());
        $this->object->addMessage('test');
        $this->assertEquals(array(PMA_Message::rawNotice('test'), ' ', PMA_Message::rawNotice('test')), $this->object->getAddedMessages());
    }

    /**
     * testing add messages method
     */
    public function testAddMessages()
    {
        $messages = array();
        $messages[] = "Test1";
        $messages[] = new PMA_Message("PMA_Test2", PMA_Message::ERROR);
        $messages[] = "Test3";
        $this->object->addMessages($messages, '');

        $this->assertEquals(array(PMA_Message::rawNotice('Test1'), PMA_Message::error("PMA_Test2"), PMA_Message::rawNotice('Test3')), $this->object->getAddedMessages());
    }

    /**
     * testing setter of params
     */
    public function testSetParams()
    {
        $this->object->setParams('test&<>');
        $this->assertEquals('test&<>', $this->object->getParams());
        $this->object->setParams('test&<>', true);
        $this->assertEquals('test&amp;&lt;&gt;', $this->object->getParams());
    }

    /**
     * testing sanitize method
     */
    public function testSanitize()
    {
        $this->object->setString('test&string<>', false);
        $this->assertEquals('test&amp;string&lt;&gt;', PMA_Message::sanitize($this->object));
        $this->assertEquals(array('test&amp;string&lt;&gt;', 'test&amp;string&lt;&gt;'), PMA_Message::sanitize(array($this->object, $this->object)));
    }

    public function decodeBBDataProvider()
    {
        return array(
            array(
                '[i]test[/i][i]aa[i/][em]test[/em]',
                '<em>test</em><em>aa[i/]<em>test</em>'
            ),
            array(
                '[b]test[/b][strong]test[/strong]',
                '<strong>test</strong><strong>test</strong>'
            ),
            array(
                '[tt]test[/tt][code]test[/code]',
                '<code>test</code><code>test</code>'
            ),
            array(
                '[kbd]test[/kbd][br][sup]test[/sup]',
                '<kbd>test</kbd><br /><sup>test</sup>'
            ),
            array(
                '[a@http://foo.bar/@Documentation]link[/a]',
                '<a href="./url.php?url=http%3A%2F%2Ffoo.bar%2F&amp;lang=en&amp;token=token" target="Documentation">link</a>'
            ),
            array(
                '[a@./non-existing@Documentation]link[/a]',
                '[a@./non-existing@Documentation]link</a>'
            ),
            array(
                '[a@./Documentation.html@Documentation]link[/a]',
                '<a href="./Documentation.html" target="Documentation">link</a>'
            ),
        );
    }

    /**
     * testing decodeBB method
     * @dataProvider decodeBBDataProvider
     */

    public function testDecodeBB($actual, $expected)
    {
        $GLOBALS['lang'] = 'en';
        $_SESSION[' PMA_token '] = 'token';
        $this->assertEquals($expected, PMA_Message::decodeBB($actual));
    }

    /**
     * testing format method
     */
    public function testFormat()
    {
        $this->assertEquals('test string', PMA_Message::format('test string'));
        $this->assertEquals('test string', PMA_Message::format('test string', 'a'));
        $this->assertEquals('test string', PMA_Message::format('test string', array()));
        $this->assertEquals('test string', PMA_Message::format('%s string', array('test')));

    }

    /**
     * testing getHash method
     */
    public function testGetHash()
    {
        $this->object->setString('<&>test', false);
        $this->object->setMessage('<&>test', false);
        $this->assertEquals(md5(PMA_Message::NOTICE . '<&>test<&>test'), $this->object->getHash());
    }

    /**
     * getMessage test - with empty message and with non-empty string - not key in globals
     * additional params are defined
     */
    public function testGetMessageWithoutMessageWithStringWithParams()
    {
        $this->object->setMessage('');
        $this->object->setString('test string %s %s');
        $this->object->addParam('test param 1');
        $this->object->addParam('test param 2');
        $this->assertEquals('test string test param 1 test param 2', $this->object->getMessage());
    }

    /**
     * getMessage test - with empty message and with empty string
     */
    public function testGetMessageWithoutMessageWithEmptyString()
    {
        $this->object->setMessage('');
        $this->object->setString('');
        $this->assertEquals('', $this->object->getMessage());
    }

    /**
     * getMessage test - with empty message and with string, which is key to GLOBALS
     * additional messages are defined
     */
    public function testGetMessageWithoutMessageWithGlobalStringWithAddMessages()
    {
        $GLOBALS['key'] = 'test message';
        $this->object->setMessage('');
        $this->object->setString('key');
        $this->object->addMessage('test message 2', ' - ');
        $this->object->addMessage('test message 3', '&');
        $this->assertEquals('test message - test message 2&test message 3', $this->object->getMessage());
        unset($GLOBALS['key']);
    }

    /**
     * getMessage test - message is defined
     * message with BBCode defined
     */
    public function testGetMessageWithMessageWithBBCode()
    {
        $this->object->setMessage('[kbd]test[/kbd] [a@./Documentation.html#cfg_Example@_blank]test[/a]');
        $this->assertEquals('<kbd>test</kbd> <a href="./Documentation.html#cfg_Example" target="_blank">test</a>', $this->object->getMessage());
    }

    /**
     * getLevel test
     */
    public function testGetLevel()
    {
        $this->assertEquals('notice', $this->object->getLevel());
        $this->object->setNumber(PMA_Message::SUCCESS);
        $this->assertEquals('success', $this->object->getLevel());
        $this->object->setNumber(PMA_Message::ERROR);
        $this->assertEquals('error', $this->object->getLevel());
    }

    /**
     * testing display method (output string and _is_displayed varible)
     */
    public function testDisplay()
    {
        $this->assertFalse($this->object->isDisplayed());
        $this->object->setMessage('Test Message');

        $this->expectOutputString('<div class="notice">Test Message</div>');
        $this->object->display();

        $this->assertTrue($this->object->isDisplayed());
    }

    /**
     * getDisplay test
     */
    public function testGetDisplay()
    {
        $this->object->setMessage('Test Message');
        $this->assertEquals('<div class="notice">Test Message</div>', $this->object->getDisplay());
    }

    /**
     * isDisplayed test
     */
    public function testIsDisplayed()
    {
        $this->assertFalse($this->object->isDisplayed(false));
        $this->assertTrue($this->object->isDisplayed(true));
        $this->assertTrue($this->object->isDisplayed(false));
    }

    public function providerAffectedRows(){
        return array(array(1, '<div class="notice"> 1 row affected.</div>'));
        return array(array(2, '<div class="notice"> 2 rows affected.</div>'));
        return array(array(50000000000000, '<div class="notice"> 50000000000000 rows affected.</div>'));
    }

    /**
     * affected_rows test
     *
     * @dataProvider providerAffectedRows
     */
    public function testAffectedRows($rows, $output)
    {
        $this->object = new PMA_Message();
        $msg = $this->object->affected_rows($rows);
        echo $this->object->addMessage($msg);
        $this->expectOutputString($output);
        $this->object->display();
    }

    public function providerInsertedRows(){
        return array(array(1, '<div class="notice"> 1 row inserted.</div>'));
        return array(array(2, '<div class="notice"> 2 rows inserted.</div>'));
        return array(array(50000000000000, '<div class="notice"> 50000000000000 rows inserted.</div>'));
    }

    /**
     * inserted_rows test
     *
     * @dataProvider providerInsertedRows
     */
    public function testInsertedRows($rows, $output)
    {
        $this->object = new PMA_Message();
        $msg = $this->object->inserted_rows($rows);
        echo $this->object->addMessage($msg);
        $this->expectOutputString($output);
        $this->object->display();
    }

    public function providerDeletedRows(){
        return array(array(1, '<div class="notice"> 1 row deleted.</div>'));
        return array(array(2, '<div class="notice"> 2 rows deleted.</div>'));
        return array(array(50000000000000, '<div class="notice"> 50000000000000 rows deleted.</div>'));
    }

    /**
     * deleted_rows test
     *
     * @dataProvider providerDeletedRows
     */
    public function testDeletedRows($rows, $output)
    {
        $this->object = new PMA_Message();
        $msg = $this->object->deleted_rows($rows);
        echo $this->object->addMessage($msg);
        $this->expectOutputString($output);
        $this->object->display();
    }
}
?>
