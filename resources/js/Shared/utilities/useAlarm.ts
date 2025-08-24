import { Howl } from "howler";
import { useEventable } from "./eventBus"; // Adjust the path if needed

export function useAlarm() {
  // Initialize the alarm sound
  const alarm = new Howl({
    src: ["/alarm.mp3"], // Path to the alarm sound in Laravel's public directory
    volume: 1.0, // Volume level (0.0 to 1.0)
    loop: false, // Set to true if the alarm should loop
  });

  // Play the alarm
  const playAlarm = () => {
    alarm.play();
  };

  // Stop the alarm
  const stopAlarm = () => {
    alarm.stop();
  };

  // Set up event listeners for alarm control
  const setupAlarmListeners = () => {
    const { watchEvent } = useEventable();

    // Watch for the "play-alarm" event
    watchEvent("play-alarm", () => {
      playAlarm();
    });

    // Watch for the "stop-alarm" event
    watchEvent("stop-alarm", () => {
      stopAlarm();
    });
  };

  // Return the API for the composable
  return {
    playAlarm,
    stopAlarm,
    setupAlarmListeners,
  };
}
