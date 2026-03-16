package com.snackzar.app.webview;

import android.content.Context;
import android.content.pm.PackageInfo;
import android.content.pm.PackageManager;
import android.os.Build;
import android.webkit.JavascriptInterface;

public class JavaScriptInterface {

    private Context context;

    public JavaScriptInterface(Context context) {
        this.context = context;
    }

    @JavascriptInterface
    public String getPlatform() {
        return "android";
    }

    @JavascriptInterface
    public String getAppVersion() {
        try {
            PackageInfo pInfo = context.getPackageManager()
                    .getPackageInfo(context.getPackageName(), 0);
            return pInfo.versionName;
        } catch (PackageManager.NameNotFoundException e) {
            return "1.0";
        }
    }

    @JavascriptInterface
    public String getDeviceInfo() {
        return Build.MODEL + " - " + Build.MANUFACTURER;
    }

    @JavascriptInterface
    public String getOSVersion() {
        return Build.VERSION.RELEASE;
    }

    @JavascriptInterface
    public boolean isOnline() {
        android.net.ConnectivityManager cm = (android.net.ConnectivityManager)
                context.getSystemService(Context.CONNECTIVITY_SERVICE);
        android.net.NetworkInfo activeNetwork = cm.getActiveNetworkInfo();
        return activeNetwork != null && activeNetwork.isConnectedOrConnecting();
    }

    @JavascriptInterface
    public void shareContent(String title, String text, String url) {
        android.content.Intent shareIntent = new android.content.Intent(
                android.content.Intent.ACTION_SEND);
        shareIntent.setType("text/plain");
        shareIntent.putExtra(android.content.Intent.EXTRA_SUBJECT, title);
        shareIntent.putExtra(android.content.Intent.EXTRA_TEXT, text + " " + url);
        context.startActivity(android.content.Intent.createChooser(shareIntent, "Share via"));
    }

    @JavascriptInterface
    public void openDialer(String phoneNumber) {
        android.content.Intent intent = new android.content.Intent(
                android.content.Intent.ACTION_DIAL);
        intent.setData(android.net.Uri.parse("tel:" + phoneNumber));
        context.startActivity(intent);
    }

    @JavascriptInterface
    public void sendEmail(String email) {
        android.content.Intent intent = new android.content.Intent(
                android.content.Intent.ACTION_SENDTO);
        intent.setData(android.net.Uri.parse("mailto:" + email));
        context.startActivity(intent);
    }

    @JavascriptInterface
    public void logAnalytics(String event, String data) {
        android.util.Log.d("SnackzarAnalytics", event + ": " + data);
    }
}
