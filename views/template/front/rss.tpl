<rss version="2.0">
  <channel>
    <title><![CDATA[{$shop_name}]]></title>
    <description><![CDATA[{$metas['description']}]]></description>
    <link>{$shop_uri}</link>
    <generator>PrestaShop</generator>
    <webMaster>{$shop_email}</webMaster>
    <language>{$language_iso}</language>
    <image>
      <title><![CDATA[{$shop_name}]]></title>
      <url>{$logo}</url>
      <link>{$shop_uri}</link>
    </image>
    {foreach from=$products item=product}
      <item>
        <title><![CDATA[{$product['name'] nofilter} - {$product['price'] nofilter}]]></title>
        <description><![CDATA[<img src="{$product['cover']['bySize']['small_default']['url'] nofilter}" title="{$product['name'] nofilter}" alt="thumb" />{$product['description_short'] nofilter}]]></description>
        <link><![CDATA[{$product['link'] nofilter}]]></link>
      </item>
    {/foreach}
  </channel>
</rss>
