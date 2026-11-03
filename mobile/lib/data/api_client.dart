import 'dart:convert';

import 'package:http/http.dart' as http;

import '../models/motorcycle.dart';
import 'catalog_seed.dart';

class ApiClient {
  ApiClient({this.baseUrl = 'http://127.0.0.1:8010/api/v1'});

  final String baseUrl;

  Future<List<Motorcycle>> fetchMotorcycles() async {
    try {
      final res = await http
          .get(Uri.parse('$baseUrl/catalog/motorcycles'))
          .timeout(const Duration(seconds: 4));
      if (res.statusCode != 200) {
        return seedCatalog();
      }
      final body = jsonDecode(res.body) as Map<String, dynamic>;
      final list = body['data'] as List<dynamic>;
      return list
          .map((e) => Motorcycle.fromJson(Map<String, dynamic>.from(e as Map)))
          .toList();
    } catch (_) {
      return seedCatalog();
    }
  }
}
