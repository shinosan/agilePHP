/** @var string ゲームサーバのアドレス */
const SERVER_ADDRESS = 'ws://localhost';

/** @var int ゲームサーバのポート番号 */
const SERVER_PORT = 8282;

/** @var WebSocket 接続情報 */
var conn = null;

/**
 * ゲームサーバへの接続
 */
function open() {

	// 接続情報の生成
	conn = new WebSocket(SERVER_ADDRESS + ':' + SERVER_PORT);

	/** 
	 * 接続時のイベントハンドラを登録
	 * @param any e イベント情報
	 */
	conn.onopen = function (e) {
		// TODO: 初期データのロードなど
	};

	/**
	 * エラー発生時のイベントハンドラを登録
	 * @param Event<any> e イベント情報
	 */
	conn.onerror = function (e) {
		alert("エラーが発生しました");
	};

	/**
	 * サーバからのデータ受信時のイベントハンドラを登録
	 * @param MessageEvent<any> e イベント情報
	 */
	conn.onmessage = function (e) {
		// TODO: 受信したデータをパースし、Mapを再描画してFitureを配置する
		var data = JSON.parse(e.data);
		var divObj = document.createElement("DIV");
		if (data["position"] == "left") {
			divObj.className = 'receive-msg-left';
		} else {
			divObj.className = 'receive-msg-right';
		}
		var msgSplit = data["msg"].split('\n');
		for (var i in msgSplit) {
			var msg = document.createTextNode(msgSplit[i]);
			var rowObj = document.createElement("DIV");
			rowObj.appendChild(msg);
			divObj.appendChild(rowObj);
		}

		var msgLog = document.getElementById("msg_log");
		msgLog.appendChild(divObj);

		var br = document.createElement("BR");
		br.className = 'br';
		msgLog.appendChild(br);

		msgLog.scrollTop = msgLog.scrollHeight;

	};

	/**
	 * 接続断の時のイベントハンドラを登録
	 */
	conn.onclose = function () {
		alert("切断しました");
	};

}

/**
 * 送信ボタン
 */
function send() {
	// TODO:タイマーで一定間隔でプレイヤーの入力を送信する
	conn.send(document.getElementById("msg").value);
}

/**
 * 接続断ボタン
 */
function close() {
	conn.close();
}

open();
