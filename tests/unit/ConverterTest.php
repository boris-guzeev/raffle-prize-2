<?php
use \app\models\Converter;

class ConverterTest extends \Codeception\Test\Unit
{
    use Codeception\AssertThrows;
    /**
     * @var \UnitTester
     */
    protected $tester;
    
    protected function _before()
    {
    }

    protected function _after()
    {
    }

    /**
     * при входных числовых параметрах, метод всегда должен возващать целое положительное число
     */
    public function testIntegerPoints()
    {
        $converter = new Converter();
        $ratio = Yii::$app->params['rules']['ratio'];
        // параметр decimal
        for ($i = 0.1; $i < 10000; $i += 0.1) {
            $this->assertIsInt($converter->toPoints($i, $ratio));
        }
        // параметр целое число
        for ($i = 1; $i <= 10000; $i++) {
            $this->assertIsInt($converter->toPoints($i, $ratio));
        }
    }

    /**
     * метод должен правильно считать баллы
     */
    public function testCorrectNumber()
    {
        // берём произвольные числа и коэф
        $actual = 1000 * 1.5;
        $actual = intval(round($actual));
        $converter = new Converter();
        $this->assertSame($converter->toPoints(1000, 1.5), $actual);

        $actual = 34567 * 2.4567;
        $actual = intval(round($actual));
        $converter = new Converter();
        $this->assertSame($converter->toPoints(34567, 2.4567), $actual);
    }

    /**
     * при не числовых и отрицательных значениях, метод должен выбрасывать исключение
     */
    public function testMoneyExceptions()
    {
        $this->assertThrows(new \Exception, function () {
            $converter = new Converter();
            $ratio = Yii::$app->params['rules']['ratio'];
            $converter->toPoints(0, $ratio);
        });

        $this->assertThrows(new \Exception, function () {
            $converter = new Converter();
            $converter->toPoints(1000, 'test');
        });
    }
}