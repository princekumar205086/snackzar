package com.snackzar.app.webview;

import android.content.Intent;
import android.net.Uri;
import android.webkit.WebResourceRequest;
import android.webkit.WebResourceResponse;
import android.webkit.WebView;
import android.webkit.WebViewClient;

import com.snackzar.app.MainActivity;

import java.io.IOException;
import java.io.InputStream;

public class SnackzarWebViewClient extends WebViewClient {

    private MainActivity activity;

    public SnackzarWebViewClient(MainActivity activity) {
        this.activity = activity;
    }

    @Override
    public boolean shouldOverrideUrlLoading(WebView view, WebResourceRequest request) {
        String url = request.getUrl().toString();

        // Allow internal Snackzar URLs
        if (url.startsWith("https://snackzar.com") || 
            url.startsWith("https://www.snackzar.com") ||
            url.startsWith("http://snackzar.com")) {
            view.loadUrl(url);
            return true;
        }

        // Handle external URLs in external browser
        if (url.startsWith("http://") || url.startsWith("https://")) {
            Intent intent = new Intent(Intent.ACTION_VIEW, Uri.parse(url));
            activity.startActivity(intent);
            return true;
        }

        // Handle special schemes
        if (url.startsWith("tel:")) {
            Intent intent = new Intent(Intent.ACTION_DIAL, Uri.parse(url));
            activity.startActivity(intent);
            return true;
        }

        if (url.startsWith("mailto:")) {
            Intent intent = new Intent(Intent.ACTION_SENDTO, Uri.parse(url));
            activity.startActivity(intent);
            return true;
        }

        if (url.startsWith("whatsapp:")) {
            Intent intent = new Intent(Intent.ACTION_VIEW, Uri.parse(url));
            activity.startActivity(intent);
            return true;
        }

        return false;
    }

    @Override
    public void onPageFinished(WebView view, String url) {
        super.onPageFinished(view, url);
        // Inject custom JavaScript if needed
        view.evaluateJavascript(
            "javascript:(function() { " +
            "window.snackzarApp = { platform: 'android', version: '1.0' }; " +
            "})()", null);
    }

    @Override
    public void onReceivedError(WebView view, int errorCode, String description, String failingUrl) {
        super.onReceivedError(view, errorCode, description, failingUrl);
        // Load error page
        view.loadData(
            "<html><head><title>Error</title></head>" +
            "<body style=\"font-family: sans-serif; margin: 20px;\">" +
            "<h1>Unable to Load Page</h1>" +
            "<p>Error: " + description + "</p>" +
            "<p>URL: " + failingUrl + "</p>" +
            "<button onclick=\"history.back()\" style=\"padding: 10px 20px; font-size: 16px;\">" +
            "Go Back</button></body></html>",
            "text/html", "UTF-8"
        );
    }
}
