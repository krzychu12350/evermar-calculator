import Pusher from 'pusher-js';
// move credentials into .env file
const pusher = new Pusher('26143f87a08bdfeab780', {
  cluster: 'eu',
  forceTLS: true,
});

export default pusher;
