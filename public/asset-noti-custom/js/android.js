
//Change wifi or 4G
function changeNetworkImage(networkType) {
    var element = document.getElementById('wifi');

    if (networkType === 'wifi') {
        element.src = '/image/wifi.png';
    } else if (networkType === '4G') {
        element.src = '/image/4G.png';
    }
}
changeNetworkImage('4G');
// changeNetworkImage('wifi');


//Change battery
var element = document.getElementById('battery');
element.src = '/image/batteryIndicator.png';


//
function updateContent(data) {
    document.getElementById('time').textContent = "Thời gian: " + data.time;
    document.getElementById('account').textContent = "Tài khoản: " + data.account;
    document.getElementById('transaction').textContent = "Giao dịch: " + data.transaction;
    document.getElementById('balance').textContent = "Số dư hiện tại: " + data.balance;
    document.getElementById('content').textContent = "Nội dung: " + data.content;
}

updateContent({
    time: '20/05/2024 22:39',
    account: '123456789012',
    transaction: '+10,000 VND',
    balance: '50,000 VND',
    content: 'CT DEN: 123456789012 OKBILL.NET chuyen FT12345678901234; tai Napas'
});


//
document.getElementById('time2').textContent = "Thời gian: " + '10:00 AM';

//
document.getElementById('time3').textContent = "Thời gian: " + '10:00 AM';