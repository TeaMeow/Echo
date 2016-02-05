<p align="center">
  x
</p>
<p align="center">
  <i>Hello from the other side.</i>
</p>

&nbsp;

# Speaker

擴音器是一個基於 [Server-Sent Event](http://www.html5rocks.com/en/tutorials/eventsource/basics/) 服務的 PHP 函式庫的，用以向客戶端廣播不間斷且持續性連線地單向訊息，

很適合用在類似推特（Twitter）或是聊天室這樣需要推播即時通知的網站。

&nbsp;

# 特色

1. 簡潔的使用方式

2. 支援多個事件監聽器 

3. 不到 50 行程式碼即可做成一個聊天室，[按下這裡查看範例](example/chatroom/server.php)。

&nbsp;

# 教學

# 範例

首先你需要初始化擴音器。

```php
$speaker = new Speaker();
```

&nbsp;

接著，你需要**新增事件監測器**，用來檢測你的伺服器**是否有新的資料要推送**。

你需要在新增一個（也可以多個）專門給予 SSE（Server-Sent Event）的專屬類別，

並且**該類別要延伸於 `SpeakerEvents`**。

1. `update()` 是**用來回傳資料的地方**，只有在 `check()` 回傳 `true` 的時候才會被呼叫。

2. `check()` 是**用來確認是否有新的資料**，假設 `check()` 回傳 `true`，那麼 `update()` 就會被執行。

    所以這個函式應該要用來放置條件式，確認資料庫是否有新的資料，倘若有，則回傳 `true` 等。

```php
class FoobarHandler extends SpeakerEvents
{
    function update()
    {
        return json_encode(['foo' => 'bar']);
    }
    
    function check()
    {
        return true;
    }
}
```

&nbsp;

然後向擴音器註冊這一個事件監聽器。

```php
$speaker->addListener('', new FoobarHandler());
```

&nbsp;

然後開始擴音（也就是開始你的 Server-Sent Event）。

```php
$speaker->start();
```

&nbsp;

# 可參考文件

[licson0729@libSSE-php](https://github.com/licson0729/libSSE-php)

[igorw@EventSource](https://github.com/igorw/EventSource)
