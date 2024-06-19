// Change content message
document.addEventListener('DOMContentLoaded', function() {
    //eventDetails
    const eventDetails = {
        time: '20/05/2024 22:39',
        account: '123456789012',
        transaction: '+10,000 VND',
        balance: '50,000 VND'
    };

    document.getElementById('time').innerText = `Thời gian: ${eventDetails.time}`;
    document.getElementById('account').innerText = `Tài khoản: ${eventDetails.account}`;
    document.getElementById('transaction').innerText = `Giao dịch: ${eventDetails.transaction}`;
    document.getElementById('balance').innerText = `Số dư hiện tại: ${eventDetails.balance}`;

    //eventDetails2
    const eventDetails2 = {
        time: '20/05/2024 22:39',
        account: '123456789012',
        transaction: '+10,000 VND',
        balance: '50,000 VND'
    };

    document.getElementById('time2').innerText = `Thời gian: ${eventDetails2.time}`;
    document.getElementById('account2').innerText = `Tài khoản: ${eventDetails2.account}`;
    document.getElementById('transaction2').innerText = `Giao dịch: ${eventDetails2.transaction}`;
    document.getElementById('balance2').innerText = `Số dư hiện tại: ${eventDetails2.balance}`;

    //eventDetails3
    const eventDetails3 = {
        time: '20/05/2024 22:39',
        account: '123456789012',
        transaction: '+10,000 VND',
        balance: '50,000 VND'
    };

    document.getElementById('time3').innerText = `Thời gian: ${eventDetails3.time}`;
    document.getElementById('account3').innerText = `Tài khoản: ${eventDetails3.account}`;
    document.getElementById('transaction3').innerText = `Giao dịch: ${eventDetails3.transaction}`;
    document.getElementById('balance3').innerText = `Số dư hiện tại: ${eventDetails3.balance}`;
});


//Change countTime
var element = document.getElementById('countTime');
element.innerHTML = '12:57';

//Change wifi or 4G
function changeNetworkImage(networkType) {
    var element = document.getElementById('wifi');

    if (networkType === 'wifi') {
        element.src = '/image/wifi.png';
    } else if (networkType === '4G') {
        element.src = '/image/4G.png';
    }
}
// changeNetworkImage('4G');
changeNetworkImage('wifi');

//Change battery
var element = document.getElementById('battery');
element.src = '/image/batteryIndicator.png';

