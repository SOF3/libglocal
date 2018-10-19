// This is a generated file. Not intended for manual editing.
package io.github.sof3.libglocal.intellij.parser;

import com.intellij.psi.tree.IElementType;
import com.intellij.psi.PsiElement;
import com.intellij.lang.ASTNode;
import io.github.sof3.libglocal.intellij.psi.impl.*;

public interface LgcElements {

  IElementType ARGUMENT_ATTRIBUTE_VALUE = new LgcElementType("ARGUMENT_ATTRIBUTE_VALUE");
  IElementType ARG_DOC = new LgcElementType("ARG_DOC");
  IElementType ARG_FIELD = new LgcElementType("ARG_FIELD");
  IElementType ARG_MODIFIER = new LgcElementType("ARG_MODIFIER");
  IElementType ARG_REF_COMPONENT = new LgcElementType("ARG_REF_COMPONENT");
  IElementType ARITHMETIC_PREDICATE = new LgcElementType("ARITHMETIC_PREDICATE");
  IElementType ATTRIBUTE = new LgcElementType("ATTRIBUTE");
  IElementType ATTRIBUTE_VALUE = new LgcElementType("ATTRIBUTE_VALUE");
  IElementType AUTHOR_BLOCK = new LgcElementType("AUTHOR_BLOCK");
  IElementType DOC_MODIFIER = new LgcElementType("DOC_MODIFIER");
  IElementType END = new LgcElementType("END");
  IElementType LANG_BLOCK = new LgcElementType("LANG_BLOCK");
  IElementType LITERAL = new LgcElementType("LITERAL");
  IElementType LITERAL_ATTRIBUTE_VALUE = new LgcElementType("LITERAL_ATTRIBUTE_VALUE");
  IElementType MATH_COMPARATOR = new LgcElementType("MATH_COMPARATOR");
  IElementType MATH_RULE_BLOCK = new LgcElementType("MATH_RULE_BLOCK");
  IElementType MESSAGE_ATTRIBUTE_VALUE = new LgcElementType("MESSAGE_ATTRIBUTE_VALUE");
  IElementType MESSAGE_BLOCK = new LgcElementType("MESSAGE_BLOCK");
  IElementType MESSAGE_GROUP = new LgcElementType("MESSAGE_GROUP");
  IElementType MESSAGE_REF_COMPONENT = new LgcElementType("MESSAGE_REF_COMPONENT");
  IElementType MODULE_BLOCK = new LgcElementType("MODULE_BLOCK");
  IElementType NUMBER_ATTRIBUTE_VALUE = new LgcElementType("NUMBER_ATTRIBUTE_VALUE");
  IElementType REQUIRE_BLOCK = new LgcElementType("REQUIRE_BLOCK");
  IElementType SPAN_COMPONENT = new LgcElementType("SPAN_COMPONENT");
  IElementType STATIC_LITERAL = new LgcElementType("STATIC_LITERAL");
  IElementType USE_BLOCK = new LgcElementType("USE_BLOCK");
  IElementType VERSION_BLOCK = new LgcElementType("VERSION_BLOCK");
  IElementType VERSION_MODIFIER = new LgcElementType("VERSION_MODIFIER");

  IElementType ARG_REF_START = new LgcTokenType("ARG_REF_START");
  IElementType ATTRIBUTE_SIMPLE_MESSAGE = new LgcTokenType("ATTRIBUTE_SIMPLE_MESSAGE");
  IElementType CLOSE_BRACE = new LgcTokenType("CLOSE_BRACE");
  IElementType COMMENT = new LgcTokenType("comment");
  IElementType CONT_CONCAT = new LgcTokenType("CONT_CONCAT");
  IElementType CONT_NEWLINE = new LgcTokenType("CONT_NEWLINE");
  IElementType CONT_SPACE = new LgcTokenType("CONT_SPACE");
  IElementType EOL = new LgcTokenType("EOL");
  IElementType EQUALS = new LgcTokenType("EQUALS");
  IElementType FLAG = new LgcTokenType("FLAG");
  IElementType IDENTIFIER = new LgcTokenType("IDENTIFIER");
  IElementType INDENT = new LgcTokenType("indent");
  IElementType INDENT_DECREASE = new LgcTokenType("INDENT_DECREASE");
  IElementType INDENT_INCREASE = new LgcTokenType("INDENT_INCREASE");
  IElementType INVALID_CLOSE_BRACE = new LgcTokenType("<invalid }>");
  IElementType INVALID_INDENT = new LgcTokenType("<invalid indent>");
  IElementType LITERAL_ESCAPE = new LgcTokenType("LITERAL_ESCAPE");
  IElementType LITERAL_STRING = new LgcTokenType("LITERAL_STRING");
  IElementType MATH_AT = new LgcTokenType("MATH_AT");
  IElementType MATH_EQ = new LgcTokenType("MATH_EQ");
  IElementType MATH_GE = new LgcTokenType("MATH_GE");
  IElementType MATH_GT = new LgcTokenType("MATH_GT");
  IElementType MATH_LE = new LgcTokenType("MATH_LE");
  IElementType MATH_LT = new LgcTokenType("MATH_LT");
  IElementType MATH_MOD = new LgcTokenType("MATH_MOD");
  IElementType MATH_NE = new LgcTokenType("MATH_NE");
  IElementType MATH_SEPARATOR = new LgcTokenType("MATH_SEPARATOR");
  IElementType MESSAGE_REF_START = new LgcTokenType("MESSAGE_REF_START");
  IElementType MISSING_WHITESPACE = new LgcTokenType("<whitespace missing>");
  IElementType MOD_ARG = new LgcTokenType("MOD_ARG");
  IElementType MOD_DOC = new LgcTokenType("MOD_DOC");
  IElementType MOD_VERSION = new LgcTokenType("MOD_VERSION");
  IElementType NUMBER = new LgcTokenType("NUMBER");
  IElementType OPEN_BRACE = new LgcTokenType("OPEN_BRACE");
  IElementType SPAN_NAME = new LgcTokenType("SPAN_NAME");
  IElementType SPAN_START = new LgcTokenType("SPAN_START");
  IElementType WHITESPACE = new LgcTokenType("whitespace");
  IElementType _ABSTRACT_MESSAGE_BLOCK_ = new LgcTokenType("<abstract message block>");
  IElementType _META_BLOCK_ = new LgcTokenType("<meta block>");

  class Factory {
    public static PsiElement createElement(ASTNode node) {
      IElementType type = node.getElementType();
       if (type == ARGUMENT_ATTRIBUTE_VALUE) {
        return new LgcArgumentAttributeValueImpl(node);
      }
      else if (type == ARG_DOC) {
        return new LgcArgDocImpl(node);
      }
      else if (type == ARG_FIELD) {
        return new LgcArgFieldImpl(node);
      }
      else if (type == ARG_MODIFIER) {
        return new LgcArgModifierImpl(node);
      }
      else if (type == ARG_REF_COMPONENT) {
        return new LgcArgRefComponentImpl(node);
      }
      else if (type == ARITHMETIC_PREDICATE) {
        return new LgcArithmeticPredicateImpl(node);
      }
      else if (type == ATTRIBUTE) {
        return new LgcAttributeImpl(node);
      }
      else if (type == ATTRIBUTE_VALUE) {
        return new LgcAttributeValueImpl(node);
      }
      else if (type == AUTHOR_BLOCK) {
        return new LgcAuthorBlockImpl(node);
      }
      else if (type == DOC_MODIFIER) {
        return new LgcDocModifierImpl(node);
      }
      else if (type == END) {
        return new LgcEndImpl(node);
      }
      else if (type == LANG_BLOCK) {
        return new LgcLangBlockImpl(node);
      }
      else if (type == LITERAL) {
        return new LgcLiteralImpl(node);
      }
      else if (type == LITERAL_ATTRIBUTE_VALUE) {
        return new LgcLiteralAttributeValueImpl(node);
      }
      else if (type == MATH_COMPARATOR) {
        return new LgcMathComparatorImpl(node);
      }
      else if (type == MATH_RULE_BLOCK) {
        return new LgcMathRuleBlockImpl(node);
      }
      else if (type == MESSAGE_ATTRIBUTE_VALUE) {
        return new LgcMessageAttributeValueImpl(node);
      }
      else if (type == MESSAGE_BLOCK) {
        return new LgcMessageBlockImpl(node);
      }
      else if (type == MESSAGE_GROUP) {
        return new LgcMessageGroupImpl(node);
      }
      else if (type == MESSAGE_REF_COMPONENT) {
        return new LgcMessageRefComponentImpl(node);
      }
      else if (type == MODULE_BLOCK) {
        return new LgcModuleBlockImpl(node);
      }
      else if (type == NUMBER_ATTRIBUTE_VALUE) {
        return new LgcNumberAttributeValueImpl(node);
      }
      else if (type == REQUIRE_BLOCK) {
        return new LgcRequireBlockImpl(node);
      }
      else if (type == SPAN_COMPONENT) {
        return new LgcSpanComponentImpl(node);
      }
      else if (type == STATIC_LITERAL) {
        return new LgcStaticLiteralImpl(node);
      }
      else if (type == USE_BLOCK) {
        return new LgcUseBlockImpl(node);
      }
      else if (type == VERSION_BLOCK) {
        return new LgcVersionBlockImpl(node);
      }
      else if (type == VERSION_MODIFIER) {
        return new LgcVersionModifierImpl(node);
      }
      throw new AssertionError("Unknown element type: " + type);
    }
  }
}
