# php-validate

一个简单小巧的php验证库。仅有几个文件，无依赖。

> 规则设置参考自 yii 的。

## 安装

- 使用 composer

编辑 `composer.json`，在 `require` 添加

```
"inhere/php-validate": "dev-master",
```

然后执行: `composer update`

- 直接拉取

```
git clone https://git.oschina.net/inhere/php-validate.git // git@osc
git clone https://github.com/inhere/php-validate.git // github
```

## 使用

<a name="how-to-use"></a>
### 使用方式 1: 创建一个新的class

创建一个新的class，并继承 `inhere\validate\Validation`。 此方式是最为完整的使用方式

```php

    use inhere\validate\Validation;

    class PageRequest extends Validation
    {
        public function rules()
        {
            return [
                ['tagId,title,userId,freeTime', 'required', 'msg' => '{attr} is required!'],
                ['tagId', 'size', 'min'=>4, 'max'=>567], // 4<= tagId <=567
                ['title', 'min', 'min' => 40],
                ['freeTime', 'number'],
                ['tagId', 'number', 'when' => function($data) {
                    return isset($data['status']) && $data['status'] > 2;
                }],
                ['userId', 'number', 'on' => 'scene1' ],
                ['username', 'string', 'on' => 'scene2' ],
                ['title', 'customValidator', 'msg' => '{attr} error msg!' ],
                ['status', function($status)
                {

                    if ($status > 3) {
                        return true;
                    }
                    return false;
                }],
            ];
        }
        
        // 添加一个验证器。必须返回一个布尔值标明验证失败或成功
        protected function customValidator($title)
        {
            // some logic ...

            return true; // Or false;
        }

        // 定义字段翻译
        public function attrTrans()
        {
            return [
              'userId' => '用户Id',
            ];
        }

        // 自定义验证器的提示消息, 更多请看 {@see ValidationTrait::_defaultMessages}
        public function messages()
        {
            return [
              'required' => '{attr} 是必填项。',
            ];
        }
    }
```

使用

```php
// 验证 POST 数据
$valid = PageRequest::make($_POST)->validate();

// 验证失败
if ($valid->fail()) {
    var_dump($valid->getErrors());
    var_dump($valid->firstError());
}

// 验证成功 ...

```

### 使用方式 2: 直接使用

需要快速简便的使用验证时，可直接使用 `inhere\validate\Validation`

```php

    use inhere\validate\Validation;

    class SomeController
    {
        public function demoAction()
        {
            $valid = Validation::make($_POST,[
                // add rule
                ['title', 'min', 'min' => 40],
                ['freeTime', 'number'],
            ])->validate();

            if ($valid->fail()) {
                var_dump($valid->getErrors());
                var_dump($valid->firstError());
            }

            //
            // some logic ... ...
        }
    }
```

## 如何添加自定义验证器

- 在继承了 `inhere\validate\Validation` 的子类添加验证方法. 请看上面的 **使用方式1**
- 通过 `Validation::addValidator()` 添加自定义验证器. e.g:

```php

$valid = Validation::make($_POST,[
        // add rule
        ['title', 'min', 'min' => 40],
        ['freeTime', 'number'],
        ['title', 'checkTitle'],
    ])
    ->addValidator('checkTitle',function($title){
        // some logic ...

        return true; // 成功返回 True。 如果验证失败,返回 False.
    }, '{attr} default message!')
    ->validate();

```

- 直接写闭包进行验证 e.g:

```php
    ['status', function($status) {

        if ($status > 3) {
            return true;
        }
        
        return false;
    }]
```

## 规则关键词说明

### `on` -- 设置规则使用场景

> 如果需要让定义的规则在多个类似情形下重复使用，可以设置规则的使用场景。在验证时也表明要验证的场景

```php
    // 在继承了 Validation 的子类 ValidationClass 中 ...
    public function rules()
    {
         return [
            ['title', 'required' ],
            ['userId', 'number', 'on' => 'create' ],
            ['userId', 'int', 'on' => 'update' ],
            ['name', 'string', 'on' => 'create,update' ],
        ];
    }
```

使用:

如，在下面指定了验证场景时，将会使用上面的第 1,3,4 条规则. (第 1 条没有限制规则使用场景的，在所有场景都可用)

```php
    // ...
    $valid = ValidationClass::make($_POST)->useScene('update')->validate();
    // ...

```

### `when` -- 规则的前置条件

> 只有在先满足了(`when`)前置条件时才会验证这条规则

如在下面的例子中，检查到第二条规则时，会先执行闭包(`when`)，
当其返回 `true` 验证此条规则，否则不会验证此条规则

```php
    // 在继承了 Validation 的子类中 ...
    public function rules()
    {
         return [
            ['title', 'required' ],
            ['tagId', 'number', 'when' => function($data)
            {
               return isset($data['status']) && $data['status'] > 2;
            }],
        ];
    }
```

### `skipOnEmpty` -- 为空是否跳过验证

当字段值为空时是否跳过验证,默认值是 `true`. (参考自 yii2)

> 'required' 规则不在此限制内.

如,有一条规则:

```php
['name', 'string']
```

提交的数据中 没有 `name` 字段或者 `$data['name']` 等于空都不会进行 `string` 验证;
只有当 `$data['name']` 有值且不为空时才会验证是否是string


如果要想为空时也检查, 请将此字段同时加入 `required` 规则中. 

```php
['name', 'required' ]
['name', 'string' ]
```

或者也可以设置 `'skipOnEmpty' => false`:

```php
['name', 'string', 'skipOnEmpty' => false ]
```

### `isEmpty` -- 是否为空判断

是否为空判断, 这个判断作为 `skipOnEmpty` 的依据. 默认使用 `empty($data[$attr])` 来判断.

你也可以自定义判断规则:

```
['name', 'string', 'isEmpty' => function($data, $attr) {
    return true or false;
 }]
```

## 一些关键方法说明

### 设置验证场景

```php
public function setScene(string $scene)
```

### 进行数据验证

```php
public function validate(array $onlyChecked = [], $stopOnError = null)
```

进行数据验证。 返回验证器对象，然后就可以获取验证结果等信息。

- `$onlyChecked` 可以设置此次需要验证的字段
- `$stopOnError` 是否当出现一个验证失败就立即停止。 默认是 `true`

### 添加自定义的验证器

```php
public function addValidator(string $name, \Closure $callback, string $msg = '')
```

添加自定义的验证器。 返回验证器对象以支持链式调用

- `$name` 自定义验证器名称
- `$callback` 自定义验证器。处理验证，为了简洁只允许闭包。
- `$msg` 可选的。 当前验证器的错误消息

### 获取验证是否通过

```
public function hasError()
public function isFail() // hasError() 的别名方法
public function fail() // hasError() 的别名方法
```

获取验证是否通过(是否有验证失败)。

### 获取所有错误信息

```php
public function getErrors(): array
```

获取所有的错误信息, 包含所有错误的字段和错误信息的多维数组。 eg:

```php 
[
    [ attr1 => 'error message 1'],
    [ attr1 => 'error message 2'],
    [ attr2 => 'error message 3'],
]
```

> 同一个属性/字段也可能有多个错误消息，当为它添加了多个验证规则时。

### 得到第一个错误信息

```php
public function firstError($onlyMsg = true)
```

- `$onlyMsg` 是否只返回消息字符串。当为 false，返回的则是数组 eg: `[ attr => 'error message']`

### 得到最后一个错误信息

```php
public function lastError($onlyMsg = true)
```

- `$onlyMsg` 是否只返回消息字符串。当为 false，返回的则是数组 eg: `[ attr => 'error message']`

### 获取所有数据

```php
public function all(): array
```

获取验证时传入的所有数据

### 根据字段名获取值

```php
public function get(string $key, $default = null)
```

从验证时传入的数据中取出对应 key 的值

### 获取所有验证通过的数据

```php
public function getSafeData(): array
```

获取所有 **验证通过** 的安全数据

> 注意： 当有验证失败出现时，安全数据 `safeData` 将会被重置为空。 即只有全部通过验证，才能获取到 `safeData`

### 根据字段名获取安全值

```php
public function getSafe(string $key, $default = null)
public function getValid(string $key, $default = null) // getSafe() 的别名方法
```

从 **验证通过** 的数据中取出对应 key 的值

## 内置的验证器

验证器 | 说明 | 规则示例
----------|-------------|------------
`int`   | 验证是否是 int | `['userId', 'int']`
`number`    | 验证是否是 number | `['userId', 'number']`
`bool`  | 验证是否是 bool | `['open', 'bool']`
`float` | 验证是否是 float | `['price', 'float']`
`string`    | 验证是否是 string. 支持长度检查 | `['name', 'string']`, `['name', 'string', 'min'=>4, 'max'=>16]`
`isArray`   | 验证是否是数组 | ....
`regexp`    | 使用正则进行验证 | ....
`url`   | 验证是否是 url | `['myUrl', 'url']`
`email` | 验证是否是 email | `['userEmail', 'email']`
`ip`    | 验证是否是 ip | `['ipAddr', 'ip']`
`required`  | 要求此字段/属性是必须的 | `['tagId, userId', 'required' ]`
`size`  | 验证大小范围, 可以支持验证 `int`, `string`, `array` 数据类型 | `['tagId', 'size', 'min'=>4, 'max'=>567]` `['name', 'size', 'max' => 16]`
`range`  | `size` 验证的别名 | 跟 `size` 一样
`length`    | 长度验证（ 跟 `size`差不多, 但只能验证 `string`, `array` 的长度 | ....
`min`   | 最小边界值验证 | `['title', 'min', 'value' => 40]`
`max`   | 最大边界值验证 | `['title', 'max', 'value' => 40]`
`in`    | 枚举验证 | `['id', 'in', 'value' => [1,2,3]`
`compare` | 字段值比较 | `['passwd', 'compare', 'repasswd']`
`callback`  | 自定义回调验证 | ....

- 关于布尔值验证
    * 如果是 "1"、"true"、"on" 和 "yes"，则返回 TRUE
    * 如果是 "0"、"false"、"off"、"no" 和 ""，则返回 FALSE
- 验证大小范围 `int` 是比较大小。 `string` 和 `array` 是检查长度

## 其他

可运行示例请看 `example` 

## License

MIT
