package com.snackzar.app.webview;

import android.webkit.WebChromeClient;
import android.webkit.WebView;

public class SnackzarWebChromeClient extends WebChromeClient {

    @Override
    public void onProgressChanged(WebView view, int newProgress) {
        super.onProgressChanged(view, newProgress);
        // You can update progress bar here if needed
    }

    @Override
    public boolean onConsoleMessage(android.webkit.ConsoleMessage consoleMessage) {
        // Log console messages from WebView
        android.util.Log.d(
            "SnackzarApp",
            "JS: " + consoleMessage.message() + " (line: " + consoleMessage.lineNumber() + ")"
        );
        return true;
    }

    @Override
    public void onShowCustomView(android.view.View view, WebChromeClient.CustomViewCallback callback) {
        super.onShowCustomView(view, callback);
        // Handle fullscreen video or other custom views
    }

    @Override
    public void onHideCustomView() {
        super.onHideCustomView();
    }
}
