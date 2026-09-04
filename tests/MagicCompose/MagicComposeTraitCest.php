<?php

declare(strict_types=1);

/**
 * MagicComposeTraitCest.php
 *
 * PHP Version 8.4
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\MagicCompose\Tests\MagicCompose;

use Blackcube\MagicCompose\Tests\Support\ChildModelWithParent;
use Blackcube\MagicCompose\Tests\Support\MagicComposeTester;
use Blackcube\MagicCompose\Tests\Support\TestModel;

/**
 * Unit tests for MagicComposeTrait.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
final class MagicComposeTraitCest
{
    public function testGetterHighPriorityCalledFirst(MagicComposeTester $I): void
    {
        $model = new TestModel();

        $result = $model->highPriorityProp;

        $I->assertEquals('high', $result);
        $I->assertContains('getHighPriority', $model->callLog);
        $I->assertNotContains('getNormalPriority-shouldNotBeCalled', $model->callLog);
    }

    public function testGetterNormalPriority(MagicComposeTester $I): void
    {
        $model = new TestModel();

        $result = $model->normalPriorityProp;

        $I->assertEquals('normal', $result);
        $I->assertContains('getNormalPriority', $model->callLog);
    }

    public function testGetterLowPriority(MagicComposeTester $I): void
    {
        $model = new TestModel();

        $result = $model->lowPriorityProp;

        $I->assertEquals('low', $result);
        $I->assertContains('getLowPriority', $model->callLog);
    }

    public function testGetterVirtualProperty(MagicComposeTester $I): void
    {
        $model = new TestModel();

        $result = $model->virtualProperty;

        $I->assertEquals('initial', $result);
        $I->assertContains('getVirtual', $model->callLog);
    }

    public function testGetterPriorityOrder(MagicComposeTester $I): void
    {
        $model = new TestModel();

        $model->highPriorityProp;

        $I->assertCount(1, $model->callLog);
        $I->assertEquals('getHighPriority', $model->callLog[0]);
    }

    public function testSetterHighPriority(MagicComposeTester $I): void
    {
        $model = new TestModel();

        $model->highPriorityProp = 'new-value';

        $I->assertContains('setHighPriority', $model->callLog);
        $I->assertEquals('new-value', $model->getData()['highPriorityProp']);
    }

    public function testSetterVirtualProperty(MagicComposeTester $I): void
    {
        $model = new TestModel();

        $model->virtualProperty = 'updated';

        $I->assertContains('setVirtual', $model->callLog);
        $I->assertEquals('updated', $model->virtualProperty);
    }

    public function testIssetVirtualPropertyTrue(MagicComposeTester $I): void
    {
        $model = new TestModel();

        $result = isset($model->virtualProperty) === true;

        $I->assertTrue($result);
        $I->assertContains('issetVirtual', $model->callLog);
    }

    public function testIssetUndefinedPropertyFalse(MagicComposeTester $I): void
    {
        $model = new TestModel();

        $result = isset($model->undefinedProperty) === true;

        $I->assertFalse($result);
        $I->assertContains('issetUndefined', $model->callLog);
    }

    public function testUnsetVirtualProperty(MagicComposeTester $I): void
    {
        $model = new TestModel();
        $model->virtualProperty = 'some-value';
        $model->clearCallLog();

        unset($model->virtualProperty);

        $I->assertContains('unsetVirtual', $model->callLog);
        $I->assertEquals('', $model->virtualProperty);
    }

    public function testCallHighPriorityMethod(MagicComposeTester $I): void
    {
        $model = new TestModel();

        $result = $model->highPriorityMethod('a', 'b');

        $I->assertEquals('high-a-b', $result);
        $I->assertContains('callHighPriority', $model->callLog);
    }

    public function testCallVirtualMethod(MagicComposeTester $I): void
    {
        $model = new TestModel();

        $result = $model->virtualMethod('x', 'y', 'z');

        $I->assertEquals('virtual-x-y-z', $result);
        $I->assertContains('callVirtual', $model->callLog);
    }

    public function testFallbackToParentGet(MagicComposeTester $I): void
    {
        $model = new ChildModelWithParent();

        $model->unknownProp;

        $I->assertContains('parent::__get(unknownProp)', $model->parentCallLog);
    }

    public function testFallbackToParentSet(MagicComposeTester $I): void
    {
        $model = new ChildModelWithParent();

        $model->unknownProp = 'value';

        $I->assertContains('parent::__set(unknownProp)', $model->parentCallLog);
    }

    public function testFallbackToParentIsset(MagicComposeTester $I): void
    {
        $model = new ChildModelWithParent();

        isset($model->unknownProp);

        $I->assertContains('parent::__isset(unknownProp)', $model->parentCallLog);
    }

    public function testFallbackToParentUnset(MagicComposeTester $I): void
    {
        $model = new ChildModelWithParent();

        unset($model->unknownProp);

        $I->assertContains('parent::__unset(unknownProp)', $model->parentCallLog);
    }

    public function testFallbackToParentCall(MagicComposeTester $I): void
    {
        $model = new ChildModelWithParent();

        $result = $model->unknownMethod();

        $I->assertEquals('parent-unknownMethod', $result);
        $I->assertContains('parent::__call(unknownMethod)', $model->parentCallLog);
    }

    public function testChildHandlerBeforeParent(MagicComposeTester $I): void
    {
        $model = new ChildModelWithParent();

        $result = $model->childProp;

        $I->assertEquals('child-value', $result);
        $I->assertContains('getChildProperty', $model->childCallLog);
        $I->assertNotContains('parent::__get(childProp)', $model->parentCallLog);
    }

    public function testNoParentGetTriggersWarning(MagicComposeTester $I): void
    {
        $model = new TestModel();
        $warningTriggered = false;
        $originalHandler = set_error_handler(function ($errno, $errstr) use (&$warningTriggered) {
            if ($errno === E_USER_WARNING && str_contains($errstr, 'Undefined property') === true) {
                $warningTriggered = true;
            }
            return true;
        });

        $model->nonExistentProperty;

        restore_error_handler();
        $I->assertTrue($warningTriggered);
    }

    public function testNoParentSetTriggersWarning(MagicComposeTester $I): void
    {
        $model = new TestModel();
        $warningTriggered = false;
        set_error_handler(function ($errno, $errstr) use (&$warningTriggered) {
            if ($errno === E_USER_WARNING && str_contains($errstr, 'Undefined property') === true) {
                $warningTriggered = true;
            }
            return true;
        });

        $model->nonExistentProperty = 'value';

        restore_error_handler();
        $I->assertTrue($warningTriggered);
    }

    public function testNoParentCallTriggersError(MagicComposeTester $I): void
    {
        $model = new TestModel();

        $I->expectThrowable(
            new \BadMethodCallException('Call to undefined method '.TestModel::class.'::nonExistentMethod()'),
            function () use ($model) {
                $model->nonExistentMethod();
            }
        );
    }

    public function testNotHandledExceptionContinuesToNextHandler(MagicComposeTester $I): void
    {
        $model = new TestModel();

        $result = $model->normalPriorityProp;

        $I->assertEquals('normal', $result);
        $I->assertNotContains('getHighPriority', $model->callLog);
        $I->assertContains('getNormalPriority', $model->callLog);
    }

    public function testNotHandledExceptionChainAllHandlers(MagicComposeTester $I): void
    {
        $model = new TestModel();

        $result = $model->lowPriorityProp;

        $I->assertEquals('low', $result);
        $I->assertContains('getLowPriority', $model->callLog);
    }

    public function testNotHandledExceptionSetterChain(MagicComposeTester $I): void
    {
        $model = new TestModel();

        $model->virtualProperty = 'chained';

        $I->assertEquals('chained', $model->virtualProperty);
        $I->assertContains('setVirtual', $model->callLog);
        $I->assertNotContains('setHighPriority', $model->callLog);
    }

    public function testNotHandledExceptionCallChain(MagicComposeTester $I): void
    {
        $model = new TestModel();

        $result = $model->virtualMethod('a', 'b');

        $I->assertEquals('virtual-a-b', $result);
        $I->assertContains('callVirtual', $model->callLog);
        $I->assertNotContains('callHighPriority', $model->callLog);
    }

    public function testNotHandledExceptionIssetChain(MagicComposeTester $I): void
    {
        $model = new TestModel();

        $result = isset($model->virtualProperty) === true;

        $I->assertTrue($result);
        $I->assertContains('issetVirtual', $model->callLog);
        $I->assertNotContains('issetHighPriority', $model->callLog);
    }

    public function testIssetHighPriorityHandled(MagicComposeTester $I): void
    {
        $model = new TestModel();
        $model->highPriorityProp = 'exists';
        $model->clearCallLog();

        $result = isset($model->highPriorityProp) === true;

        $I->assertTrue($result);
        $I->assertContains('issetHighPriority', $model->callLog);
        $I->assertNotContains('issetVirtual', $model->callLog);
    }

    public function testNotHandledExceptionUnsetChain(MagicComposeTester $I): void
    {
        $model = new TestModel();
        $model->virtualProperty = 'test';
        $model->clearCallLog();

        unset($model->virtualProperty);

        $I->assertEquals('', $model->virtualProperty);
        $I->assertContains('unsetVirtual', $model->callLog);
        $I->assertNotContains('unsetHighPriority', $model->callLog);
    }

    public function testUnsetHighPriorityHandled(MagicComposeTester $I): void
    {
        $model = new TestModel();
        $model->highPriorityProp = 'to-delete';
        $model->clearCallLog();

        unset($model->highPriorityProp);

        $I->assertContains('unsetHighPriority', $model->callLog);
        $I->assertNotContains('unsetVirtual', $model->callLog);
    }

    public function testCacheParentMagicMethodsMultipleInstances(MagicComposeTester $I): void
    {
        $model1 = new ChildModelWithParent();
        $model1->unknownProp;
        $I->assertContains('parent::__get(unknownProp)', $model1->parentCallLog);

        $model2 = new ChildModelWithParent();
        $model2->unknownProp;
        $I->assertContains('parent::__get(unknownProp)', $model2->parentCallLog);

        $I->assertCount(1, $model1->parentCallLog);
        $I->assertCount(1, $model2->parentCallLog);
    }

    public function testCacheHandlersMultipleCalls(MagicComposeTester $I): void
    {
        $model = new TestModel();

        $result1 = $model->highPriorityProp;
        $result2 = $model->highPriorityProp;
        $result3 = $model->highPriorityProp;

        $I->assertEquals('high', $result1);
        $I->assertEquals('high', $result2);
        $I->assertEquals('high', $result3);

        $I->assertCount(3, array_filter($model->callLog, fn($log) => $log === 'getHighPriority'));
    }

    public function testCacheParentMethodDetectionNoParent(MagicComposeTester $I): void
    {
        $model1 = new TestModel();
        $model2 = new TestModel();

        $warningCount = 0;
        set_error_handler(function ($errno) use (&$warningCount) {
            if ($errno === E_USER_WARNING) {
                $warningCount++;
            }
            return true;
        });

        $model1->nonExistentProperty;
        $model2->nonExistentProperty;

        restore_error_handler();

        $I->assertEquals(2, $warningCount);
    }

    public function testCacheParentMethodDetectionWithParent(MagicComposeTester $I): void
    {
        $model1 = new ChildModelWithParent();
        $model2 = new ChildModelWithParent();

        $model1->unknownProp;
        $model2->anotherUnknown;

        $I->assertContains('parent::__get(unknownProp)', $model1->parentCallLog);
        $I->assertContains('parent::__get(anotherUnknown)', $model2->parentCallLog);
    }
}
