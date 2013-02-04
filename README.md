Плагин Conpay.ru для VirtueMart 2 on Joomla! 2.5
================================================

## Установка

1. Установите плагин через менеджер расширений.
2. Перейдите на страницу настроек плагина (Расширения -> Менеджер плагинов -> Сервис CONPAY.RU).
3. Включите плагин и задайте настройки для api Conpay.ru, согласно [руководству](https://www.conpay.ru/profile/merchant/info/install).
4. Укажите в настройках плагина ID-атрибут контейнера, в котором будет распологаться кнопка.
5. Добавьте этот контейнер в шаблон страницы товара и корзины:
```
<span id="conpay-container-id"></span>
```


## Детали

Плагин (кнопка "Купить в кредит") появится на страницах товаров, для которых значение поля "Купить в кредит" выставлено в "Да" (Компонент VirtueMart -> Товары -> Выбранный товар -> Настраиваемые поля). По-умолчанию кнопка появляется на страницах товаров стоимостью свыше 3000 руб.

## Удаление плагина

1. Переходим в Менеджер расширений (Расширения -> Менеджер расширений).
2. Выбираем подменю "Управление".
3. Выбираем и удаляем плагин.
