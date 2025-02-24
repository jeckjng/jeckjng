<?php
/**
 * 请在下面完成你的语言配置
 */

return array(
    // 易盾短信
    'yd' => array(
        'CN' => '86',
        'PH' => '63',
        'HK' => '852',
        'TWN' => '886',
        'MO' => '853',
        'SA' => '966',
        'AE' => '971',
        'NZ' => '64',

        'UZ' => '998',
        'YE' => '967',
        'AM' => '374',
        'IL' => '972',
        'IQ' => '964',
        'IR' => '98',
        'QA' => '974',
        'SY' => '963',
        'KG' => '996',
        'KZ' => '76',
        'TM' => '993',
        'TJ' => '992',
        'BL' => '970',
        'PK' => '92',
        'BH' => '973',
        'KW' => '965',
        'JO' => '962',
        'AZ' => '994',
        'AF' => '93',
        'OM' => '968',
        'LB' => '961',
        'DZ' => '213',
        'LK' => '94',
    ),
    // 云片短信
    'yp' => array(
        'CN' => '',
        'PH' => '+',
        'HK' => '+',
        'TWN' => '+',
        'MO' => '+',
        'SA' => '+',
        'AE' => '+',
        'NZ' => '+',

        'UZ' => '+',
        'YE' => '+',
        'AM' => '+',
        'IL' => '+',
        'IQ' => '+',
        'IR' => '+',
        'QA' => '+',
        'SY' => '+',
        'KG' => '+',
        'KZ' => '+',
        'TM' => '+',
        'TJ' => '+',
        'BL' => '+',
        'PK' => '+',
        'BH' => '+',
        'KW' => '+',
        'JO' => '+',
        'AZ' => '+',
        'AF' => '+',
        'OM' => '+',
        'LB' => '+',
        'DZ' => '+',
        'LK' => '+',
    ),
    // 手机号码正则表达式
    'phone_pattern' => array(
        'CN' => "/^1[3|4|5|6|7|8|9]\d{9}$/", // 中国大陆
        'PH' => "/^[1-9]\d{9}$/", // 菲律宾
        'HK' => "/^([4|5|6|9])\d{7}$/", // 香港 如：91985962
        'TWN' => "/^[0][9]\d{8}$/", // 台湾 如：
        'MO' => "/^[6]\d{7}$/", // 澳门 如：
        'SA' => "/^[5]\d{8}$/", // 沙特阿拉伯 如：
        'AE' => "/^[5]\d{8}$/", // 迪拜(阿拉伯联合酋长国) 如：
        'NZ' => "/^[2]\d{7,9}$/", // 新西兰 如：

        'UZ' => "/^[9]\d{8}$/", // 乌兹别克斯坦 如：
        'YE' => "/^[7]\d{8}$/", // 也门 如：
        'AM' => "/^[4-9]\d{7}$/", // 亚美尼亚 如：
        'IL' => "/^[5]\d{8}$/", // 以色列 如：
        'IQ' => "/^[7]\d{9}$/", // 伊拉克 如：
        'IR' => "/^[1-9]\d{7,12}$/", // 伊朗 如：
        'QA' => "/^[3|5|6|7]\d{7}$/", // 卡塔尔 如：
        'SY' => "/^[1-9]\d{7,12}$/", // 叙利亚 如：
        'KG' => "/^[2|5|7|9]\d{8}$/", // 吉尔吉斯斯坦 如：
        'KZ' => "/^[6]\d{9}$/", // 哈萨克斯坦 如：
        'TM' => "/^[6\d{7}$/", // 土库曼斯坦 如：
        'TJ' => "/^[9]\d{8}$/", // 塔吉克斯坦 如：
        'BL' => "/^[5]\d{8}$/", // 巴勒斯坦 如：
        'PK' => "/^[3]\d{9}$/", // 巴基斯坦 如：
        'BH' => "/^[3|6]\d{7}$/", // 巴林 如：
        'KW' => "/^[5|6|9]\d{7}$/", // 科威特 如：
        'JO' => "/^[7]\d{8}$/", // 约旦 如：
        'AZ' => "/^[6-7]\d{8}$/", // 阿塞拜疆 如：
        'AF' => "/^[7]\d{8}$/", // 阿富汗 如：
        'OM' => "/^[7|9]\d{7}$/", // 阿曼 如：
        'LB' => "/^[3|7|8]\d{7}$/", // 黎巴嫩 如：
        'DZ' => "/^[5-7]\d{8}$/", // 阿尔及利亚 如：
        'LK' => "/^[7]\d{8}$/", // 斯里兰卡 如：
    ),
);
