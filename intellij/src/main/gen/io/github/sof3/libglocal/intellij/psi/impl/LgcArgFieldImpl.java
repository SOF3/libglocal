// This is a generated file. Not intended for manual editing.
package io.github.sof3.libglocal.intellij.psi.impl;

import java.util.List;
import org.jetbrains.annotations.*;
import com.intellij.lang.ASTNode;
import com.intellij.psi.PsiElement;
import com.intellij.psi.PsiElementVisitor;
import com.intellij.psi.util.PsiTreeUtil;
import static io.github.sof3.libglocal.intellij.parser.LgcElements.*;
import com.intellij.extapi.psi.ASTWrapperPsiElement;
import io.github.sof3.libglocal.intellij.psi.*;

public class LgcArgFieldImpl extends ASTWrapperPsiElement implements LgcArgField {

  public LgcArgFieldImpl(@NotNull ASTNode node) {
    super(node);
  }

  public void accept(@NotNull LgcVisitor visitor) {
    visitor.visitArgField(this);
  }

  public void accept(@NotNull PsiElementVisitor visitor) {
    if (visitor instanceof LgcVisitor) accept((LgcVisitor)visitor);
    else super.accept(visitor);
  }

  @Override
  @NotNull
  public List<LgcArgDoc> getArgDocList() {
    return PsiTreeUtil.getChildrenOfTypeAsList(this, LgcArgDoc.class);
  }

  @Override
  @NotNull
  public List<LgcArgField> getArgFieldList() {
    return PsiTreeUtil.getChildrenOfTypeAsList(this, LgcArgField.class);
  }

  @Override
  @Nullable
  public LgcAttributeValue getAttributeValue() {
    return findChildByClass(LgcAttributeValue.class);
  }

  @Override
  @Nullable
  public PsiElement getEol() {
    return findChildByType(EOL);
  }

  @Override
  @Nullable
  public PsiElement getEquals() {
    return findChildByType(EQUALS);
  }

  @Override
  @Nullable
  public PsiElement getIndentDecrease() {
    return findChildByType(INDENT_DECREASE);
  }

  @Override
  @Nullable
  public PsiElement getIndentIncrease() {
    return findChildByType(INDENT_INCREASE);
  }

  @Override
  @NotNull
  public PsiElement getModArg() {
    return findNotNullChildByType(MOD_ARG);
  }

}
