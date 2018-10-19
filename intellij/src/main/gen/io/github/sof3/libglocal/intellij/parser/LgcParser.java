// This is a generated file. Not intended for manual editing.
package io.github.sof3.libglocal.intellij.parser;

import com.intellij.lang.PsiBuilder;
import com.intellij.lang.PsiBuilder.Marker;
import static io.github.sof3.libglocal.intellij.parser.LgcElements.*;
import static com.intellij.lang.parser.GeneratedParserUtilBase.*;
import com.intellij.psi.tree.IElementType;
import com.intellij.lang.ASTNode;
import com.intellij.psi.tree.TokenSet;
import com.intellij.lang.PsiParser;
import com.intellij.lang.LightPsiParser;

@SuppressWarnings({"SimplifiableIfStatement", "UnusedAssignment"})
public class LgcParser implements PsiParser, LightPsiParser {

  public ASTNode parse(IElementType t, PsiBuilder b) {
    parseLight(t, b);
    return b.getTreeBuilt();
  }

  public void parseLight(IElementType t, PsiBuilder b) {
    boolean r;
    b = adapt_builder_(t, b, this, null);
    Marker m = enter_section_(b, 0, _COLLAPSE_, null);
    if (t == ARG_DOC) {
      r = arg_doc(b, 0);
    }
    else if (t == ARG_FIELD) {
      r = arg_field(b, 0);
    }
    else if (t == ARG_MODIFIER) {
      r = arg_modifier(b, 0);
    }
    else if (t == ARG_REF_COMPONENT) {
      r = arg_ref_component(b, 0);
    }
    else if (t == ARGUMENT_ATTRIBUTE_VALUE) {
      r = argument_attribute_value(b, 0);
    }
    else if (t == ARITHMETIC_PREDICATE) {
      r = arithmetic_predicate(b, 0);
    }
    else if (t == ATTRIBUTE) {
      r = attribute(b, 0);
    }
    else if (t == ATTRIBUTE_VALUE) {
      r = attribute_value(b, 0);
    }
    else if (t == AUTHOR_BLOCK) {
      r = author_block(b, 0);
    }
    else if (t == DOC_MODIFIER) {
      r = doc_modifier(b, 0);
    }
    else if (t == END) {
      r = end(b, 0);
    }
    else if (t == LANG_BLOCK) {
      r = lang_block(b, 0);
    }
    else if (t == LITERAL) {
      r = literal(b, 0);
    }
    else if (t == LITERAL_ATTRIBUTE_VALUE) {
      r = literal_attribute_value(b, 0);
    }
    else if (t == MATH_COMPARATOR) {
      r = math_comparator(b, 0);
    }
    else if (t == MATH_RULE_BLOCK) {
      r = math_rule_block(b, 0);
    }
    else if (t == MESSAGE_ATTRIBUTE_VALUE) {
      r = message_attribute_value(b, 0);
    }
    else if (t == MESSAGE_BLOCK) {
      r = message_block(b, 0);
    }
    else if (t == MESSAGE_GROUP) {
      r = message_group(b, 0);
    }
    else if (t == MESSAGE_REF_COMPONENT) {
      r = message_ref_component(b, 0);
    }
    else if (t == MODULE_BLOCK) {
      r = module_block(b, 0);
    }
    else if (t == NUMBER_ATTRIBUTE_VALUE) {
      r = number_attribute_value(b, 0);
    }
    else if (t == REQUIRE_BLOCK) {
      r = require_block(b, 0);
    }
    else if (t == SPAN_COMPONENT) {
      r = span_component(b, 0);
    }
    else if (t == STATIC_LITERAL) {
      r = static_literal(b, 0);
    }
    else if (t == USE_BLOCK) {
      r = use_block(b, 0);
    }
    else if (t == VERSION_BLOCK) {
      r = version_block(b, 0);
    }
    else if (t == VERSION_MODIFIER) {
      r = version_modifier(b, 0);
    }
    else {
      r = parse_root_(t, b, 0);
    }
    exit_section_(b, 0, m, t, r, true, TRUE_CONDITION);
  }

  protected boolean parse_root_(IElementType t, PsiBuilder b, int l) {
    return file(b, l + 1);
  }

  /* ********************************************************** */
  // <message group> | <message block>
  static boolean abstract_message_block(PsiBuilder b, int l) {
    if (!recursion_guard_(b, l, "abstract_message_block")) return false;
    if (!nextTokenIs(b, "", _MESSAGE_BLOCK_, _MESSAGE_GROUP_)) return false;
    boolean r;
    r = message_group(b, l + 1);
    if (!r) r = message_block(b, l + 1);
    return r;
  }

  /* ********************************************************** */
  // <arg field> | <arg doc>
  static boolean arg_constraint(PsiBuilder b, int l) {
    if (!recursion_guard_(b, l, "arg_constraint")) return false;
    if (!nextTokenIs(b, "", _ARG_DOC_, _ARG_FIELD_)) return false;
    boolean r;
    r = arg_field(b, l + 1);
    if (!r) r = arg_doc(b, l + 1);
    return r;
  }

  /* ********************************************************** */
  // MOD_DOC <literal>? <end>
  public static boolean arg_doc(PsiBuilder b, int l) {
    if (!recursion_guard_(b, l, "arg_doc")) return false;
    if (!nextTokenIs(b, MOD_DOC)) return false;
    boolean r;
    Marker m = enter_section_(b);
    r = consumeToken(b, MOD_DOC);
    r = r && arg_doc_1(b, l + 1);
    r = r && end(b, l + 1);
    exit_section_(b, m, ARG_DOC, r);
    return r;
  }

  // <literal>?
  private static boolean arg_doc_1(PsiBuilder b, int l) {
    if (!recursion_guard_(b, l, "arg_doc_1")) return false;
    literal(b, l + 1);
    return true;
  }

  /* ********************************************************** */
  // <arg like block>
  public static boolean arg_field(PsiBuilder b, int l) {
    if (!recursion_guard_(b, l, "arg_field")) return false;
    if (!nextTokenIs(b, _ARG_LIKE_BLOCK_)) return false;
    boolean r;
    Marker m = enter_section_(b);
    r = arg_like_block(b, l + 1);
    exit_section_(b, m, ARG_FIELD, r);
    return r;
  }

  /* ********************************************************** */
  // MOD_ARG IDENTIFIER FLAG* IDENTIFIER? (EQUALS <attribute value>)? <end> (INDENT_INCREASE <arg constraint>* INDENT_DECREASE)?
  static boolean arg_like_block(PsiBuilder b, int l) {
    if (!recursion_guard_(b, l, "arg_like_block")) return false;
    if (!nextTokenIs(b, MOD_ARG)) return false;
    boolean r;
    Marker m = enter_section_(b);
    r = consumeTokens(b, 0, MOD_ARG, IDENTIFIER);
    r = r && arg_like_block_2(b, l + 1);
    r = r && arg_like_block_3(b, l + 1);
    r = r && arg_like_block_4(b, l + 1);
    r = r && end(b, l + 1);
    r = r && arg_like_block_6(b, l + 1);
    exit_section_(b, m, null, r);
    return r;
  }

  // FLAG*
  private static boolean arg_like_block_2(PsiBuilder b, int l) {
    if (!recursion_guard_(b, l, "arg_like_block_2")) return false;
    while (true) {
      int c = current_position_(b);
      if (!consumeToken(b, FLAG)) break;
      if (!empty_element_parsed_guard_(b, "arg_like_block_2", c)) break;
    }
    return true;
  }

  // IDENTIFIER?
  private static boolean arg_like_block_3(PsiBuilder b, int l) {
    if (!recursion_guard_(b, l, "arg_like_block_3")) return false;
    consumeToken(b, IDENTIFIER);
    return true;
  }

  // (EQUALS <attribute value>)?
  private static boolean arg_like_block_4(PsiBuilder b, int l) {
    if (!recursion_guard_(b, l, "arg_like_block_4")) return false;
    arg_like_block_4_0(b, l + 1);
    return true;
  }

  // EQUALS <attribute value>
  private static boolean arg_like_block_4_0(PsiBuilder b, int l) {
    if (!recursion_guard_(b, l, "arg_like_block_4_0")) return false;
    boolean r;
    Marker m = enter_section_(b);
    r = consumeTokens(b, 0, EQUALS, _ATTRIBUTE_VALUE_);
    exit_section_(b, m, null, r);
    return r;
  }

  // (INDENT_INCREASE <arg constraint>* INDENT_DECREASE)?
  private static boolean arg_like_block_6(PsiBuilder b, int l) {
    if (!recursion_guard_(b, l, "arg_like_block_6")) return false;
    arg_like_block_6_0(b, l + 1);
    return true;
  }

  // INDENT_INCREASE <arg constraint>* INDENT_DECREASE
  private static boolean arg_like_block_6_0(PsiBuilder b, int l) {
    if (!recursion_guard_(b, l, "arg_like_block_6_0")) return false;
    boolean r;
    Marker m = enter_section_(b);
    r = consumeToken(b, INDENT_INCREASE);
    r = r && arg_like_block_6_0_1(b, l + 1);
    r = r && consumeToken(b, INDENT_DECREASE);
    exit_section_(b, m, null, r);
    return r;
  }

  // <arg constraint>*
  private static boolean arg_like_block_6_0_1(PsiBuilder b, int l) {
    if (!recursion_guard_(b, l, "arg_like_block_6_0_1")) return false;
    while (true) {
      int c = current_position_(b);
      if (!arg_constraint(b, l + 1)) break;
      if (!empty_element_parsed_guard_(b, "arg_like_block_6_0_1", c)) break;
    }
    return true;
  }

  /* ********************************************************** */
  // <arg like block>
  public static boolean arg_modifier(PsiBuilder b, int l) {
    if (!recursion_guard_(b, l, "arg_modifier")) return false;
    if (!nextTokenIs(b, _ARG_LIKE_BLOCK_)) return false;
    boolean r;
    Marker m = enter_section_(b);
    r = arg_like_block(b, l + 1);
    exit_section_(b, m, ARG_MODIFIER, r);
    return r;
  }

  /* ********************************************************** */
  // ARG_REF_START IDENTIFIER <attribute>* CLOSE_BRACE
  public static boolean arg_ref_component(PsiBuilder b, int l) {
    if (!recursion_guard_(b, l, "arg_ref_component")) return false;
    if (!nextTokenIs(b, ARG_REF_START)) return false;
    boolean r;
    Marker m = enter_section_(b);
    r = consumeTokens(b, 0, ARG_REF_START, IDENTIFIER);
    r = r && arg_ref_component_2(b, l + 1);
    r = r && consumeToken(b, CLOSE_BRACE);
    exit_section_(b, m, ARG_REF_COMPONENT, r);
    return r;
  }

  // <attribute>*
  private static boolean arg_ref_component_2(PsiBuilder b, int l) {
    if (!recursion_guard_(b, l, "arg_ref_component_2")) return false;
    while (true) {
      int c = current_position_(b);
      if (!attribute(b, l + 1)) break;
      if (!empty_element_parsed_guard_(b, "arg_ref_component_2", c)) break;
    }
    return true;
  }

  /* ********************************************************** */
  // IDENTIFIER
  public static boolean argument_attribute_value(PsiBuilder b, int l) {
    if (!recursion_guard_(b, l, "argument_attribute_value")) return false;
    if (!nextTokenIs(b, IDENTIFIER)) return false;
    boolean r;
    Marker m = enter_section_(b);
    r = consumeToken(b, IDENTIFIER);
    exit_section_(b, m, ARGUMENT_ATTRIBUTE_VALUE, r);
    return r;
  }

  /* ********************************************************** */
  // MATH_SEPARATOR (MATH_MOD NUMBER)? <math comparator> NUMBER
  public static boolean arithmetic_predicate(PsiBuilder b, int l) {
    if (!recursion_guard_(b, l, "arithmetic_predicate")) return false;
    if (!nextTokenIs(b, MATH_SEPARATOR)) return false;
    boolean r;
    Marker m = enter_section_(b);
    r = consumeToken(b, MATH_SEPARATOR);
    r = r && arithmetic_predicate_1(b, l + 1);
    r = r && math_comparator(b, l + 1);
    r = r && consumeToken(b, NUMBER);
    exit_section_(b, m, ARITHMETIC_PREDICATE, r);
    return r;
  }

  // (MATH_MOD NUMBER)?
  private static boolean arithmetic_predicate_1(PsiBuilder b, int l) {
    if (!recursion_guard_(b, l, "arithmetic_predicate_1")) return false;
    arithmetic_predicate_1_0(b, l + 1);
    return true;
  }

  // MATH_MOD NUMBER
  private static boolean arithmetic_predicate_1_0(PsiBuilder b, int l) {
    if (!recursion_guard_(b, l, "arithmetic_predicate_1_0")) return false;
    boolean r;
    Marker m = enter_section_(b);
    r = consumeTokens(b, 0, MATH_MOD, NUMBER);
    exit_section_(b, m, null, r);
    return r;
  }

  /* ********************************************************** */
  // IDENTIFIER EQUALS <attribute value>
  public static boolean attribute(PsiBuilder b, int l) {
    if (!recursion_guard_(b, l, "attribute")) return false;
    if (!nextTokenIs(b, IDENTIFIER)) return false;
    boolean r;
    Marker m = enter_section_(b);
    r = consumeTokens(b, 0, IDENTIFIER, EQUALS, _ATTRIBUTE_VALUE_);
    exit_section_(b, m, ATTRIBUTE, r);
    return r;
  }

  /* ********************************************************** */
  // <literal attribute value> | <number attribute value> | <argument attribute value> | <message attribute value>
  public static boolean attribute_value(PsiBuilder b, int l) {
    if (!recursion_guard_(b, l, "attribute_value")) return false;
    boolean r;
    Marker m = enter_section_(b, l, _NONE_, ATTRIBUTE_VALUE, "<attribute value>");
    r = literal_attribute_value(b, l + 1);
    if (!r) r = number_attribute_value(b, l + 1);
    if (!r) r = argument_attribute_value(b, l + 1);
    if (!r) r = message_attribute_value(b, l + 1);
    exit_section_(b, l, m, r, false, null);
    return r;
  }

  /* ********************************************************** */
  // "author" EQUALS <static literal> <end>
  public static boolean author_block(PsiBuilder b, int l) {
    if (!recursion_guard_(b, l, "author_block")) return false;
    boolean r;
    Marker m = enter_section_(b, l, _NONE_, AUTHOR_BLOCK, "<author block>");
    r = consumeToken(b, "author");
    r = r && consumeTokens(b, 0, EQUALS, _STATIC_LITERAL_, _END_);
    exit_section_(b, l, m, r, false, null);
    return r;
  }

  /* ********************************************************** */
  // MOD_DOC <literal>? <end>
  public static boolean doc_modifier(PsiBuilder b, int l) {
    if (!recursion_guard_(b, l, "doc_modifier")) return false;
    if (!nextTokenIs(b, MOD_DOC)) return false;
    boolean r;
    Marker m = enter_section_(b);
    r = consumeToken(b, MOD_DOC);
    r = r && doc_modifier_1(b, l + 1);
    r = r && end(b, l + 1);
    exit_section_(b, m, DOC_MODIFIER, r);
    return r;
  }

  // <literal>?
  private static boolean doc_modifier_1(PsiBuilder b, int l) {
    if (!recursion_guard_(b, l, "doc_modifier_1")) return false;
    literal(b, l + 1);
    return true;
  }

  /* ********************************************************** */
  // EOL | <<eof>>
  public static boolean end(PsiBuilder b, int l) {
    if (!recursion_guard_(b, l, "end")) return false;
    boolean r;
    Marker m = enter_section_(b, l, _NONE_, END, "<end>");
    r = consumeToken(b, EOL);
    if (!r) r = eof(b, l + 1);
    exit_section_(b, l, m, r, false, null);
    return r;
  }

  /* ********************************************************** */
  // (<meta block>)+ <module block> (<abstract message block>)*
  static boolean file(PsiBuilder b, int l) {
    if (!recursion_guard_(b, l, "file")) return false;
    if (!nextTokenIs(b, _META_BLOCK_)) return false;
    boolean r;
    Marker m = enter_section_(b);
    r = file_0(b, l + 1);
    r = r && module_block(b, l + 1);
    r = r && file_2(b, l + 1);
    exit_section_(b, m, null, r);
    return r;
  }

  // (<meta block>)+
  private static boolean file_0(PsiBuilder b, int l) {
    if (!recursion_guard_(b, l, "file_0")) return false;
    boolean r;
    Marker m = enter_section_(b);
    r = meta_block(b, l + 1);
    while (r) {
      int c = current_position_(b);
      if (!meta_block(b, l + 1)) break;
      if (!empty_element_parsed_guard_(b, "file_0", c)) break;
    }
    exit_section_(b, m, null, r);
    return r;
  }

  // (<abstract message block>)*
  private static boolean file_2(PsiBuilder b, int l) {
    if (!recursion_guard_(b, l, "file_2")) return false;
    while (true) {
      int c = current_position_(b);
      if (!abstract_message_block(b, l + 1)) break;
      if (!empty_element_parsed_guard_(b, "file_2", c)) break;
    }
    return true;
  }

  /* ********************************************************** */
  // "base"? "lang" IDENTIFIER EQUALS <static literal> <end>
  public static boolean lang_block(PsiBuilder b, int l) {
    if (!recursion_guard_(b, l, "lang_block")) return false;
    boolean r;
    Marker m = enter_section_(b, l, _NONE_, LANG_BLOCK, "<lang block>");
    r = lang_block_0(b, l + 1);
    r = r && consumeToken(b, "lang");
    r = r && consumeTokens(b, 0, IDENTIFIER, EQUALS, _STATIC_LITERAL_, _END_);
    exit_section_(b, l, m, r, false, null);
    return r;
  }

  // "base"?
  private static boolean lang_block_0(PsiBuilder b, int l) {
    if (!recursion_guard_(b, l, "lang_block_0")) return false;
    consumeToken(b, "base");
    return true;
  }

  /* ********************************************************** */
  // <literal component>+
  public static boolean literal(PsiBuilder b, int l) {
    if (!recursion_guard_(b, l, "literal")) return false;
    if (!nextTokenIs(b, _LITERAL_COMPONENT_)) return false;
    boolean r;
    Marker m = enter_section_(b);
    r = literal_component(b, l + 1);
    while (r) {
      int c = current_position_(b);
      if (!literal_component(b, l + 1)) break;
      if (!empty_element_parsed_guard_(b, "literal", c)) break;
    }
    exit_section_(b, m, LITERAL, r);
    return r;
  }

  /* ********************************************************** */
  // OPEN_BRACE <literal> CLOSE_BRACE
  public static boolean literal_attribute_value(PsiBuilder b, int l) {
    if (!recursion_guard_(b, l, "literal_attribute_value")) return false;
    if (!nextTokenIs(b, OPEN_BRACE)) return false;
    boolean r;
    Marker m = enter_section_(b);
    r = consumeTokens(b, 0, OPEN_BRACE, _LITERAL_, CLOSE_BRACE);
    exit_section_(b, m, LITERAL_ATTRIBUTE_VALUE, r);
    return r;
  }

  /* ********************************************************** */
  // <static literal component> | <span component> | <arg ref component> | <message ref component>
  static boolean literal_component(PsiBuilder b, int l) {
    if (!recursion_guard_(b, l, "literal_component")) return false;
    boolean r;
    r = static_literal_component(b, l + 1);
    if (!r) r = span_component(b, l + 1);
    if (!r) r = arg_ref_component(b, l + 1);
    if (!r) r = message_ref_component(b, l + 1);
    return r;
  }

  /* ********************************************************** */
  // MATH_EQ | MATH_NE | MATH_LE | MATH_LT | MATH_GE | MATH_GT
  public static boolean math_comparator(PsiBuilder b, int l) {
    if (!recursion_guard_(b, l, "math_comparator")) return false;
    boolean r;
    Marker m = enter_section_(b, l, _NONE_, MATH_COMPARATOR, "<math comparator>");
    r = consumeToken(b, MATH_EQ);
    if (!r) r = consumeToken(b, MATH_NE);
    if (!r) r = consumeToken(b, MATH_LE);
    if (!r) r = consumeToken(b, MATH_LT);
    if (!r) r = consumeToken(b, MATH_GE);
    if (!r) r = consumeToken(b, MATH_GT);
    exit_section_(b, l, m, r, false, null);
    return r;
  }

  /* ********************************************************** */
  // MATH_AT? (MATH_AT | IDENTIFIER)? <arithmetic predicate>* <end>
  public static boolean math_rule_block(PsiBuilder b, int l) {
    if (!recursion_guard_(b, l, "math_rule_block")) return false;
    boolean r;
    Marker m = enter_section_(b, l, _NONE_, MATH_RULE_BLOCK, "<math rule block>");
    r = math_rule_block_0(b, l + 1);
    r = r && math_rule_block_1(b, l + 1);
    r = r && math_rule_block_2(b, l + 1);
    r = r && end(b, l + 1);
    exit_section_(b, l, m, r, false, null);
    return r;
  }

  // MATH_AT?
  private static boolean math_rule_block_0(PsiBuilder b, int l) {
    if (!recursion_guard_(b, l, "math_rule_block_0")) return false;
    consumeToken(b, MATH_AT);
    return true;
  }

  // (MATH_AT | IDENTIFIER)?
  private static boolean math_rule_block_1(PsiBuilder b, int l) {
    if (!recursion_guard_(b, l, "math_rule_block_1")) return false;
    math_rule_block_1_0(b, l + 1);
    return true;
  }

  // MATH_AT | IDENTIFIER
  private static boolean math_rule_block_1_0(PsiBuilder b, int l) {
    if (!recursion_guard_(b, l, "math_rule_block_1_0")) return false;
    boolean r;
    r = consumeToken(b, MATH_AT);
    if (!r) r = consumeToken(b, IDENTIFIER);
    return r;
  }

  // <arithmetic predicate>*
  private static boolean math_rule_block_2(PsiBuilder b, int l) {
    if (!recursion_guard_(b, l, "math_rule_block_2")) return false;
    while (true) {
      int c = current_position_(b);
      if (!arithmetic_predicate(b, l + 1)) break;
      if (!empty_element_parsed_guard_(b, "math_rule_block_2", c)) break;
    }
    return true;
  }

  /* ********************************************************** */
  // ATTRIBUTE_SIMPLE_MESSAGE IDENTIFIER
  public static boolean message_attribute_value(PsiBuilder b, int l) {
    if (!recursion_guard_(b, l, "message_attribute_value")) return false;
    if (!nextTokenIs(b, ATTRIBUTE_SIMPLE_MESSAGE)) return false;
    boolean r;
    Marker m = enter_section_(b);
    r = consumeTokens(b, 0, ATTRIBUTE_SIMPLE_MESSAGE, IDENTIFIER);
    exit_section_(b, m, MESSAGE_ATTRIBUTE_VALUE, r);
    return r;
  }

  /* ********************************************************** */
  // FLAG* IDENTIFIER EQUALS <literal> <end> (INDENT_INCREASE <message modifier>* INDENT_DECREASE)?
  public static boolean message_block(PsiBuilder b, int l) {
    if (!recursion_guard_(b, l, "message_block")) return false;
    if (!nextTokenIs(b, "<message block>", FLAG, IDENTIFIER)) return false;
    boolean r;
    Marker m = enter_section_(b, l, _NONE_, MESSAGE_BLOCK, "<message block>");
    r = message_block_0(b, l + 1);
    r = r && consumeTokens(b, 0, IDENTIFIER, EQUALS, _LITERAL_, _END_);
    r = r && message_block_5(b, l + 1);
    exit_section_(b, l, m, r, false, null);
    return r;
  }

  // FLAG*
  private static boolean message_block_0(PsiBuilder b, int l) {
    if (!recursion_guard_(b, l, "message_block_0")) return false;
    while (true) {
      int c = current_position_(b);
      if (!consumeToken(b, FLAG)) break;
      if (!empty_element_parsed_guard_(b, "message_block_0", c)) break;
    }
    return true;
  }

  // (INDENT_INCREASE <message modifier>* INDENT_DECREASE)?
  private static boolean message_block_5(PsiBuilder b, int l) {
    if (!recursion_guard_(b, l, "message_block_5")) return false;
    message_block_5_0(b, l + 1);
    return true;
  }

  // INDENT_INCREASE <message modifier>* INDENT_DECREASE
  private static boolean message_block_5_0(PsiBuilder b, int l) {
    if (!recursion_guard_(b, l, "message_block_5_0")) return false;
    boolean r;
    Marker m = enter_section_(b);
    r = consumeToken(b, INDENT_INCREASE);
    r = r && message_block_5_0_1(b, l + 1);
    r = r && consumeToken(b, INDENT_DECREASE);
    exit_section_(b, m, null, r);
    return r;
  }

  // <message modifier>*
  private static boolean message_block_5_0_1(PsiBuilder b, int l) {
    if (!recursion_guard_(b, l, "message_block_5_0_1")) return false;
    while (true) {
      int c = current_position_(b);
      if (!message_modifier(b, l + 1)) break;
      if (!empty_element_parsed_guard_(b, "message_block_5_0_1", c)) break;
    }
    return true;
  }

  /* ********************************************************** */
  // FLAG* IDENTIFIER EQUALS? <end> (INDENT_INCREASE <abstract message block> INDENT_DECREASE)?
  public static boolean message_group(PsiBuilder b, int l) {
    if (!recursion_guard_(b, l, "message_group")) return false;
    if (!nextTokenIs(b, "<message group>", FLAG, IDENTIFIER)) return false;
    boolean r;
    Marker m = enter_section_(b, l, _NONE_, MESSAGE_GROUP, "<message group>");
    r = message_group_0(b, l + 1);
    r = r && consumeToken(b, IDENTIFIER);
    r = r && message_group_2(b, l + 1);
    r = r && end(b, l + 1);
    r = r && message_group_4(b, l + 1);
    exit_section_(b, l, m, r, false, null);
    return r;
  }

  // FLAG*
  private static boolean message_group_0(PsiBuilder b, int l) {
    if (!recursion_guard_(b, l, "message_group_0")) return false;
    while (true) {
      int c = current_position_(b);
      if (!consumeToken(b, FLAG)) break;
      if (!empty_element_parsed_guard_(b, "message_group_0", c)) break;
    }
    return true;
  }

  // EQUALS?
  private static boolean message_group_2(PsiBuilder b, int l) {
    if (!recursion_guard_(b, l, "message_group_2")) return false;
    consumeToken(b, EQUALS);
    return true;
  }

  // (INDENT_INCREASE <abstract message block> INDENT_DECREASE)?
  private static boolean message_group_4(PsiBuilder b, int l) {
    if (!recursion_guard_(b, l, "message_group_4")) return false;
    message_group_4_0(b, l + 1);
    return true;
  }

  // INDENT_INCREASE <abstract message block> INDENT_DECREASE
  private static boolean message_group_4_0(PsiBuilder b, int l) {
    if (!recursion_guard_(b, l, "message_group_4_0")) return false;
    boolean r;
    Marker m = enter_section_(b);
    r = consumeTokens(b, 0, INDENT_INCREASE, _ABSTRACT_MESSAGE_BLOCK_, INDENT_DECREASE);
    exit_section_(b, m, null, r);
    return r;
  }

  /* ********************************************************** */
  // <arg modifier> | <doc modifier> | <version modifier>
  static boolean message_modifier(PsiBuilder b, int l) {
    if (!recursion_guard_(b, l, "message_modifier")) return false;
    boolean r;
    r = arg_modifier(b, l + 1);
    if (!r) r = doc_modifier(b, l + 1);
    if (!r) r = version_modifier(b, l + 1);
    return r;
  }

  /* ********************************************************** */
  // MESSAGE_REF_START MOD_ARG? IDENTIFIER <attribute>* CLOSE_BRACE
  public static boolean message_ref_component(PsiBuilder b, int l) {
    if (!recursion_guard_(b, l, "message_ref_component")) return false;
    if (!nextTokenIs(b, MESSAGE_REF_START)) return false;
    boolean r;
    Marker m = enter_section_(b);
    r = consumeToken(b, MESSAGE_REF_START);
    r = r && message_ref_component_1(b, l + 1);
    r = r && consumeToken(b, IDENTIFIER);
    r = r && message_ref_component_3(b, l + 1);
    r = r && consumeToken(b, CLOSE_BRACE);
    exit_section_(b, m, MESSAGE_REF_COMPONENT, r);
    return r;
  }

  // MOD_ARG?
  private static boolean message_ref_component_1(PsiBuilder b, int l) {
    if (!recursion_guard_(b, l, "message_ref_component_1")) return false;
    consumeToken(b, MOD_ARG);
    return true;
  }

  // <attribute>*
  private static boolean message_ref_component_3(PsiBuilder b, int l) {
    if (!recursion_guard_(b, l, "message_ref_component_3")) return false;
    while (true) {
      int c = current_position_(b);
      if (!attribute(b, l + 1)) break;
      if (!empty_element_parsed_guard_(b, "message_ref_component_3", c)) break;
    }
    return true;
  }

  /* ********************************************************** */
  // <lang block> | <version block> | <author block> | <require block> | <use block> | <math rule block>
  static boolean meta_block(PsiBuilder b, int l) {
    if (!recursion_guard_(b, l, "meta_block")) return false;
    boolean r;
    r = lang_block(b, l + 1);
    if (!r) r = version_block(b, l + 1);
    if (!r) r = author_block(b, l + 1);
    if (!r) r = require_block(b, l + 1);
    if (!r) r = use_block(b, l + 1);
    if (!r) r = math_rule_block(b, l + 1);
    return r;
  }

  /* ********************************************************** */
  // "module" IDENTIFIER <end>
  public static boolean module_block(PsiBuilder b, int l) {
    if (!recursion_guard_(b, l, "module_block")) return false;
    boolean r;
    Marker m = enter_section_(b, l, _NONE_, MODULE_BLOCK, "<module block>");
    r = consumeToken(b, "module");
    r = r && consumeTokens(b, 0, IDENTIFIER, _END_);
    exit_section_(b, l, m, r, false, null);
    return r;
  }

  /* ********************************************************** */
  // NUMBER
  public static boolean number_attribute_value(PsiBuilder b, int l) {
    if (!recursion_guard_(b, l, "number_attribute_value")) return false;
    if (!nextTokenIs(b, NUMBER)) return false;
    boolean r;
    Marker m = enter_section_(b);
    r = consumeToken(b, NUMBER);
    exit_section_(b, m, NUMBER_ATTRIBUTE_VALUE, r);
    return r;
  }

  /* ********************************************************** */
  // "require" IDENTIFIER <end>
  public static boolean require_block(PsiBuilder b, int l) {
    if (!recursion_guard_(b, l, "require_block")) return false;
    boolean r;
    Marker m = enter_section_(b, l, _NONE_, REQUIRE_BLOCK, "<require block>");
    r = consumeToken(b, "require");
    r = r && consumeTokens(b, 0, IDENTIFIER, _END_);
    exit_section_(b, l, m, r, false, null);
    return r;
  }

  /* ********************************************************** */
  // SPAN_START SPAN_NAME <literal> CLOSE_BRACE
  public static boolean span_component(PsiBuilder b, int l) {
    if (!recursion_guard_(b, l, "span_component")) return false;
    if (!nextTokenIs(b, SPAN_START)) return false;
    boolean r;
    Marker m = enter_section_(b);
    r = consumeTokens(b, 0, SPAN_START, SPAN_NAME, _LITERAL_, CLOSE_BRACE);
    exit_section_(b, m, SPAN_COMPONENT, r);
    return r;
  }

  /* ********************************************************** */
  // <static literal component>+
  public static boolean static_literal(PsiBuilder b, int l) {
    if (!recursion_guard_(b, l, "static_literal")) return false;
    if (!nextTokenIs(b, _STATIC_LITERAL_COMPONENT_)) return false;
    boolean r;
    Marker m = enter_section_(b);
    r = static_literal_component(b, l + 1);
    while (r) {
      int c = current_position_(b);
      if (!static_literal_component(b, l + 1)) break;
      if (!empty_element_parsed_guard_(b, "static_literal", c)) break;
    }
    exit_section_(b, m, STATIC_LITERAL, r);
    return r;
  }

  /* ********************************************************** */
  // LITERAL_STRING | LITERAL_ESCAPE | CONT_NEWLINE | CONT_SPACE | CONT_CONCAT
  static boolean static_literal_component(PsiBuilder b, int l) {
    if (!recursion_guard_(b, l, "static_literal_component")) return false;
    boolean r;
    r = consumeToken(b, LITERAL_STRING);
    if (!r) r = consumeToken(b, LITERAL_ESCAPE);
    if (!r) r = consumeToken(b, CONT_NEWLINE);
    if (!r) r = consumeToken(b, CONT_SPACE);
    if (!r) r = consumeToken(b, CONT_CONCAT);
    return r;
  }

  /* ********************************************************** */
  // "use" IDENTIFIER IDENTIFIER <end>
  public static boolean use_block(PsiBuilder b, int l) {
    if (!recursion_guard_(b, l, "use_block")) return false;
    boolean r;
    Marker m = enter_section_(b, l, _NONE_, USE_BLOCK, "<use block>");
    r = consumeToken(b, "use");
    r = r && consumeTokens(b, 0, IDENTIFIER, IDENTIFIER, _END_);
    exit_section_(b, l, m, r, false, null);
    return r;
  }

  /* ********************************************************** */
  // "version" IDENTIFIER <end>
  public static boolean version_block(PsiBuilder b, int l) {
    if (!recursion_guard_(b, l, "version_block")) return false;
    boolean r;
    Marker m = enter_section_(b, l, _NONE_, VERSION_BLOCK, "<version block>");
    r = consumeToken(b, "version");
    r = r && consumeTokens(b, 0, IDENTIFIER, _END_);
    exit_section_(b, l, m, r, false, null);
    return r;
  }

  /* ********************************************************** */
  // MOD_VERSION IDENTIFIER <end>
  public static boolean version_modifier(PsiBuilder b, int l) {
    if (!recursion_guard_(b, l, "version_modifier")) return false;
    if (!nextTokenIs(b, MOD_VERSION)) return false;
    boolean r;
    Marker m = enter_section_(b);
    r = consumeTokens(b, 0, MOD_VERSION, IDENTIFIER, _END_);
    exit_section_(b, m, VERSION_MODIFIER, r);
    return r;
  }

}
