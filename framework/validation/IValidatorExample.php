<?php


namespace Framework\Validation;


class IValidatorExample {

    public function test() {

        // 整型验证
        // $result = (new IValidator())->validate(
        //     ['number' => '1'],
        //     ['number' => 'intVal|between:10,20'],
        //     ['number.intVal' => '请输入正确的整型值', 'number.between' => '请输入1-20之间的整型值']
        // );

        // // 字符串验证
        // $result = (new IValidator())->validate(
        //     ['name' => 'Icecream'],
        //     ['name' => 'stringType|length:5,10'],
        //     ['name.stringType' => '请输入正确的名字', 'name.length' => '请输入5-10之间的长度']
        // );

        // // 数组验证
        // $result = (new IValidator())->validate(
        //     ['users' => ['Icecream', 'bob']],
        //     ['users' => 'arrayType|length:1,20'],
        //     ['users.arrayType' => '请输入数组类型', 'name.between' => '请输入1-20之间的长度']
        // );

        // 日期验证
        // $result = (new IValidator())->validate(
        //     ['date' => date('Y-m-d H:i:s')],
        //     ['date' => 'date:Y-m-d|between:2019-01-01,2019-11-10'],
        //     ['date.date' => '请输入正确的日期', 'date.between' => '请输入2019-01-01至2019-01-10之间的日期']
        // );

        // // URL验证
        // $result = (new IValidator())->validate(
        //     ['url' => 'htp://example.com'],
        //     ['url' => 'url'],
        //     ['url.url' => '请输入正确的URL']
        // );

        // // 以...开头验证
        // $result = (new IValidator())->validate(
        //     ['url' => 'http://example.com'],
        //     ['url' => 'startsWith:http'],
        //     ['url.startsWith' => '请输入以http开头的URL']
        // );

        // // 以...开头验证
        // $result = (new IValidator())->validate(
        //     ['url' => 'http://example.com'],
        //     ['url' => 'startsWith:http'],
        //     ['url.startsWith' => '请输入以http开头的URL']
        // );

        // // 以...结尾验证
        // $result = (new IValidator())->validate(
        //     ['url' => 'http://example.com'],
        //     ['url' => 'endsWith:com'],
        //     ['url.endsWith' => '请输入以com结尾的URL']
        // );

        // 正则验证
        // $result = (new IValidator())->validate(
        //     ['name' => 'Icecr1eam'],
        //     ['name' => 'regex:/^[a-z]$/'],
        //     ['name.regex' => '请输入a-z的姓名']
        // );

        // var_dump($result);
        // exit();
    }
}
