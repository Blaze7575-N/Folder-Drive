import mitt from "mitt";

export const FILE_UPLOAD_STARTED = "FILE_UPLOAD_STARTED";
export const ON_UPLOAD_ERROR = "ON_UPLOAD_ERROR";
export const SHOW_NOTIFICATION = "SHOW_NOTIFICATION";
export const emitter = mitt();

export function onErrorDialog(message) {
    emitter.emit(ON_UPLOAD_ERROR, message);
}

export function onSuccessNotification(message) {
    emitter.emit(SHOW_NOTIFICATION, { message, type: "success" });
}

export function onErrorNotification(message) {
    emitter.emit(SHOW_NOTIFICATION,{ message, type: "error" });
}
